<?php
/*
ARCHIVO MODIFICADO POR: ALEJANDRA ROSAS n_n
FECHA DE MODIFICACION: 22 DE JUNIO DE 2010
INICIO CAMBIOS
1.- SE REALIZARA LA VALIDACION Y LA CARGA DEL ARCHIVO QUE CONTRENDRA LOS DATOS NECESARIOS DE LOS CLIENTES
FIN CAMBIOS
*/

$PageSecurity = 5;

include('includes/session.inc');

$title = _('Cargar Clientes...');

include('includes/header.inc');
$funcion=750;
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
if (isset($_POST['cargar']) and $separador<>'')
{
    #almacenamiento de valores en variables temporales
    #nombre del archivo
    $nombre_archivo = $_FILES['userfile']['name'];
    #tipo del archivo que se cargara
    $tipo_archivo = $_FILES['userfile']['type'];
    #tamaño o peso del archivo
    $tamano_archivo = $_FILES['userfile']['size']; 
    #direccion en donde se cargara el archivo
    $filename = 'pricelists/'.$nombre_archivo;
    
    //echo "EL TIPO DE ARCHIVO ES:".$tipo_archivo;
     
    if ($tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='application/octet-stream')
    {
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename)){
        
            # UNA VEZ QUE EL ARCHIVO SE CARGO, ENTRA A LEER EL CONTENIDO
            
            #Declara variables
            
            $tieneerrores	= 0;
            $lineatitulo	= -1; //-1 para que no verifique columnas del titulo
            $mincolumnas	= 37;
            
            $apaterno_razonsocial	= 0;
            $amaterno				= 1;
            $nombre					= 2;
            $rfc					= 3;
            $direccion				= 4;
            $colonia				= 5;
            $ciudad					= 6;
            $estado					= 7;
            $cp						= 8;
            $dirextra				= 9;
            $tel					= 10;
            $fax					= 11;
            $email					= 12;
            $limitecredito			= 13;
            $moneda					= 14;
            $clavecliente			= 15;
            $clavearea				= 16;
            $diascredito			= 17;
            $tipocliente			= 18;
            $nota					= 19;
            $nombre2				= 20;
            $direccion2				= 21;
            $colonia2				= 22;
            $ciudad2				= 23;
            $estado2				= 24;
            $cp2					= 25;
            $nombre3				= 26;
            $direccion3				= 27;
            $colonia3				= 28;
            $ciudad3				= 29;
            $estado3				= 30;
            $cp3					= 31;
            $canal					= 32;
            $pais					= 33;
            $vendedor				= 34;
            $tipoindustria			= 35;
            $fechaalta				= 36;
            
            # ABRE EL ARCHIVO Y LO ALMACENA EN UN OBJETO    
            $lineas = file($filename);
            
            # ****************************
            # **** RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO ****
            # ****************************
            
            echo "<table width=100% cellpadding=3 border=1>";
            echo "<tr>";
              echo "<td style='text-align:center;' colspan=19>";
                echo "<font size=2 color=Darkblue><b>"._('RESULTADOS DE LA CARGA DE LOS CLIENTES')."</b></font>";
              echo "</td>";
            echo "</tr>";
           
            $cont=0;
            foreach ($lineas as $line_num => $line)
              {
              	$InputError = 0;
              	//echo "<br>LINEA: " . $line;
                $datos = explode($separador, $line); # Convierte en array cada una de las lineas
                $columnaslinea = count($datos);      # Obtiene el numero de columnas de la linea en base al separador
                
                # ****************************
                # **** PRIMERA VALIDACION ****
                # **** columnas de la linea menores que la minimas requeridas?***
                # ****************************
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
                    # ************************************************************  
                    # *** ENTRA A VALIDAR LOS TITULOS DE LAS LISTAS DE PRECIOS ***
                    # ************************************************************

                    if ($line_num==$lineatitulo)
                    {
                        $columnas = count($datos);  # Obtiene el numero de columnas de la linea en base al separador
                        $columnapreciosFin = intval($columnas-1);

                        $k=0;
                    } else {
                # *****************************************************************************
                # *** COLUMNA A.Paterno / Razon Social ***
                # *****************************************************************************                          
                    # si viene vacio la columna del A.Paterno / Razon Social
                        $codigoapaterno_razonsocial = 0;
                        if ($datos[$apaterno_razonsocial]=='')
                        {
                            $error = _('EL A.PATERNO / RAZON SOCIAL NO PUEDE IR VACIO .<br> Verificar la linea no. ') . intval($line_num+1);
                            prnMsg($error,'error');
                            exit;
                        } else {
                            if (strlen($datos[$apaterno_razonsocial]) > 255) {
                                $InputError = 1;
                                $error = _('EL A.PATERNO / RAZON SOCIAL DEBE SER MAYOR A 0 Y MENOR A 40 CARACTERES .<br> Verificar la linea no. ') . intval($line_num+1);
                                prnMsg($error,'error');
                                exit;
                            }
                            
                            #verifica que no exista un grupo llamado igual en la base de datos
                            $sqlq = "SELECT count(*)
                                FROM debtorsmaster
                                WHERE name1 = '" . strtoupper(trim(htmlspecialchars_decode($datos[$apaterno_razonsocial],ENT_NOQUOTES))) . "'
                                and name2 = '" . strtoupper(trim(htmlspecialchars_decode($datos[$amaterno],ENT_NOQUOTES))) . "'
                                and name3 = '" . strtoupper(trim(htmlspecialchars_decode($datos[$nombre],ENT_NOQUOTES))) . "'
                                and address3 = '" . strtoupper(trim(htmlspecialchars_decode($datos[$ciudad],ENT_NOQUOTES))) . "'";
                            $DbgMsg = _('El SQL que se utiliza para recuperar la información fue');
                            $ErrMsg = _('No se puede comprobar si el grupo existe porque');
                            $resultq=DB_query($sqlq, $db,$ErrMsg,$DbgMsg);
                            $myrowq=DB_fetch_row($resultq);
                            #si existe  manda mensaje de error
                            if ($myrowq[0]!=0) {
                                $InputError = 0;
                                $error = _('EL NOMBRE DEL CLIENTE YA EXISTE EN LA BASE DE DATOS ' . $datos[$apaterno_razonsocial] . " - " . trim($datos[$clavecliente]));
                                prnMsg($error,'error');
                                //exit;
                            }                
                                            
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigoapaterno_razonsocial = trim($datos[$apaterno_razonsocial]);
                                
                        }; # Fin de if 
                          
                    # *****************************************************************************
                    # *** COLUMNA RFC ***
                    # *****************************************************************************                          
                        # si viene vacio la columna del rfc
                        $codigorfc = "";
                        if ($datos[$rfc]=='')
                        {
                            $error = _('EL RFC NO PUEDE IR VACIO .<br> Verificar la linea no. ') . intval($line_num+1);
                            //prnMsg($error,'error');
                            //exit;
                        } else {
                            if (strlen($datos[$rfc]) >= 15) {
                                $error = _('EL RFC DEBE SER DE MENOS DE 15 CARACTERES.<br> Verificar la linea no. '). intval($line_num+1);
                                prnMsg($error,'error');
                                //exit;
                            }
                            if (strlen($datos[$rfc]) <= 9) {
                                $error = _('EL RFC NO PUEDE SER MENOR A 10 CARACTERES.<br> Verificar la linea no. '). intval($line_num+1);
                                prnMsg($error,'error');
                                //exit;
                            }
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigorfc = trim($datos[$rfc]);
                            
                        }; # Fin de if
                        
                    # *****************************************************************************
                    # *** COLUMNA A.Materno ***
                    # *****************************************************************************                          
                        # si viene vacio la columna del A.Materno
                        $codigoamaterno = "";
                        if ($datos[$amaterno]=='')
                        {
                            $codigoamaterno='';
                        } else {
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigoamaterno = trim($datos[$amaterno]);
                                                    
                        };# Fin de if 
                          
                    # *****************************************************************************
                    # *** COLUMNA Nombre(s) ***
                    # *****************************************************************************                          
                        # si viene vacio la columna de Nombre(s)
                        $codigonombre = "";
                        if ($datos[$nombre]=='')
                        {
                            $codigonombre='';
                        } else {
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigonombre = trim($datos[$nombre]);
                                                               
                        }; # Fin de if 
                  
                    # *****************************************************************************
                    # *** COLUMNA Direccion ***
                    # *****************************************************************************                          
                        # si viene vacio la columna del direccion
                        $codigodireccion = "";
                        if ($datos[$direccion]=='')
                        {
                            $codigodireccion='';
                        } else {
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigodireccion = trim($datos[$direccion]);
                            
                        }; # Fin de if 
                        
                    # *****************************************************************************
                    # *** COLUMNA Colonia ***
                    # *****************************************************************************                          
                        # si viene vacio la columna de colonia
                        $codigocolonia = "";
                        if ($datos[$colonia]=='')
                        {
                            $codigocolonia='';
                        } else {
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigocolonia = trim($datos[$colonia]);
                                                               
                        }; # Fin de if 
                          
                    # *****************************************************************************
                    # *** COLUMNA ciudad ***
                    # *****************************************************************************                          
                        # si viene vacio la columna de ciudad
                        $codigociudad = "";
                        if ($datos[$ciudad]=='')
                        {
                            $codigociudad='';
                        }else {
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigociudad = trim($datos[$ciudad]);
                                                               
                        }; # Fin de if trae codigo de producto
                          
                    # *****************************************************************************
                    # *** COLUMNA estado ***
                    # *****************************************************************************                          
                        # si viene vacio la columna del estado
                        $codigoestado = "";
                        if ($datos[$estado]=='')
                        {
                            $codigoestado='';
                        } else {
                            #si cumple las condiciones se almcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigoestado = trim($datos[$estado]);
                                                               
                        }; # Fin de if 
                               
                    # *****************************************************************************
                    # *** COLUMNA cp ***
                    # *****************************************************************************                          
                        # si viene vacio la columna del cp
                        $codigocp = "";
                        if ($datos[$cp]=='')
                        {
                            $codigocp='';
                        } else {
                            #si cumple las condiciones se almacena su valor en variable temporal
                            $codigovalido = 1;
                            $codigocp = trim($datos[$cp]);                                   
                            
                        }; # Fin de if 
                    # *****************************************************************************
                    # *** COLUMNA Direccion Extra ***
                    # *****************************************************************************                          
                        # si viene vacio la columna del Direccion Extra
                        $codigodirextra = "";
                        if ($datos[$dirextra]=='')
                        {
                            $codigodirextra='';
                        } else {
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigodirextra = trim($datos[$dirextra]);                               
                        
                        }; # Fin de if 
                    # *****************************************************************************
                    # *** COLUMNA Telefono ***
                    # *****************************************************************************                          
                        # si viene vacio la columna del Telefono
                        $codigotel = "";
                        if ($datos[$tel]=='')
                        {
                            $codigotel='';
                        } else {
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigotel = trim($datos[$tel]);                                   
                        }; # Fin de if 
                          
                    # *****************************************************************************
                    # *** COLUMNA tipo Fax ***
                    # *****************************************************************************                          
                      # si viene vacio la columna del fax
                        $codigofax = "";
                        if ($datos[$fax]=='')
                        {
                            $codigofax='';
                        } else {
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigofax = trim($datos[$fax]);
                            
                        }; # Fin de if
                        
                    # *****************************************************************************
                    # *** COLUMNA tipo Email ***
                    # *****************************************************************************                          
                      # si viene vacio la columna del Email
                        $codigoemail = "";
                        if ($datos[$email]=='')
                        {
                            $codigoemail='';
                        } else {
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigoemail = trim($datos[$email]);
                            
                        }; # Fin de if
                        
                    # *****************************************************************************
                    # *** COLUMNA Limite De Credito ***
                    # *****************************************************************************                          
                      # si es vacia la columna limite de credito
                        $codigolimitecredito = "";
                        if ($datos[$limitecredito]=='')
                        {
                            $codigolimitecredito=100000;
                        } else {
                            if (!is_numeric($datos[$limitecredito])) {
                                $error = _('EL limite de credito debe ser numerico.<br> Verificar la linea no. ') . intval($line_num+1);
                                prnMsg($error,'error');
                                exit;
                            }
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigolimitecredito = trim($datos[$limitecredito]);
                            
                        }; # Fin de if
                        
                    # *****************************************************************************
                    # *** COLUMNA Moneda ***
                    # *****************************************************************************                          
                        # si es vacia la columna Moneda
                        $codigomoneda = "";
                        if ($datos[$moneda]=='')
                        {
                            $codigomoneda='MXN';
                        } else {
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            $codigovalido = 1;
                            $codigomoneda = trim($datos[$moneda]);
                            
                        }; # Fin de if
                    # *****************************************************************************
                    # *** COLUMNA Clave cliente ***
                    # *****************************************************************************                          
                        # si es vacia la columna cliente
                        $codigocliente = "";
                        $clienteexiste = 0;
                        if ($datos[$clavecliente]=='')
                        {
                            $codigoclienete='';
                        } else {
                            #si cumple las condiciones se alamcena su valor en variable temporal
                            
                        	$sqlq = "SELECT count(*)
                                FROM debtorsmaster
                                WHERE debtorno = '" . trim($datos[$clavecliente]) . "'";
                        	//echo $sqlq;
                        	//exit; 
                        	$DbgMsg = _('El SQL que se utiliza para recuperar la información fue');
                        	$ErrMsg = _('No se puede comprobar si el grupo existe porque');
                        	$resultq=DB_query($sqlq, $db,$ErrMsg,$DbgMsg);
                        	$myrowq=DB_fetch_row($resultq);
                        	#si existe  manda mensaje de error
                        	
                        	if ($myrowq[0]!=0) {
                        		$InputError = 1;
                        		$error = _('EL CODIGO DEL CLIENTE YA EXISTE EN LA BASE DE DATOS ' . $datos[$apaterno_razonsocial]);
                        		prnMsg($error,'error');
                        		$clienteexiste = 1;
                        		//exit;
                        	}
                        	
                        	$codigovalido = 1;
                            $codigocliente= trim($datos[$clavecliente]);
                            
                        }; # Fin de if
                    # *****************************************************************************
                    # *** COLUMNA Clave Area ***
                    # *****************************************************************************
                        # si es vacia la columna area
                        $codigoarea = "";
                        $codigodefaultlocation = '';
                        if ($datos[$clavearea]=='')
                        {
                        	$codigoarea = '';
                        } else {
                        	#si cumple las condiciones se alamcena su valor en variable temporal
                        	$codigovalido = 1;
                        	$codigoarea = trim($datos[$clavearea]);
                        	
                        	$rs = DB_query("SELECT areacode FROM areas WHERE areadescription = '$codigoarea'", $db);
                        	if($row = DB_fetch_array($rs)) {
                        		$codigoarea = $row['areacode'];
                        	}
                        
                        }; # Fin de if
                        
                        $rs = DB_query("SELECT loccode FROM locations WHERE areacod = '$codigoarea'", $db);
                        if($row = DB_fetch_array($rs)) {
                        	$codigodefaultlocation = $row['loccode'];
                        }
                        
                    # *****************************************************************************
                    # *** COLUMNA Dias Credito ***
                    # *****************************************************************************
                        # si es vacia la columna dias credito
                        $codigodiascredito = "";
                        if ($datos[$diascredito]=='')
                        {
                        	$codigodiascredito = '';
                        } else {
                        	#si cumple las condiciones se alamcena su valor en variable temporal
                        	$codigovalido = 1;
                        	$codigodiascredito = trim($datos[$diascredito]);
                        	if (trim($codigodiascredito)==''){
                        		$codigodiascredito = '1';
                        	}
                        	$codigodiascreditotmp = $codigodiascredito;
                        	$rs = DB_query("SELECT termsindicator FROM paymentterms WHERE daysbeforedue = '$codigodiascredito'", $db);
                        	$codigodiascredito = '00';
                        	if($row = DB_fetch_array($rs)) {
                        		$codigodiascredito = $row['termsindicator'];
                        	}
                        
                        }; # Fin de if
                     # *****************************************************************************
                     # *** COLUMNA Dias Credito ***
                     # *****************************************************************************
                     # si es vacia la columna dias credito
                        $codigotipocliente = "";
                        if ($datos[$tipocliente]=='')
                        {
                        	$codigotipocliente = '';
                        } else {
                        	#si cumple las condiciones se alamcena su valor en variable temporal
                        	$codigovalido = 1;
                        	$codigotipocliente = trim($datos[$tipocliente]);
                        	/*
                        	$codigotipoclientetmp = $codigotipocliente;
                        	
                        	switch ($codigotipoclientetmp){
                        		case "NAL":
                        			$codigotipocliente = "12";
                        			break;
                        		case "MXEXT":
                        			$codigotipocliente = "10";
                        			break;
                        		case "EXT":
                        			$codigotipocliente = "11";
                        			break;
                        		case "EXTMX":
                        			$codigotipocliente = "8";
                        			break;
                        	}
                        	*/
                        
                        }; # Fin de if
                        # *****************************************************************************
                        # *** COLUMNA Nota ***
                        # *****************************************************************************
                        # si es vacia la columna dias credito
                        $codigonota = "";
                        if ($datos[$nota]=='')
                        {
                        	$codigonota = '';
                        } else {
                        	#si cumple las condiciones se alamcena su valor en variable temporal
                        	$codigovalido = 1;
                        	$codigonota = trim($datos[$nota]);
                        	
                        }; # Fin de if
                        
                        
                        $codigonombre2 = trim($datos[$nombre2]);
                        $codigodireccion2 = trim($datos[$direccion2]);
                        $codigocolonia2 = trim($datos[$colonia2]);
                        $codigociudad2 = trim($datos[$ciudad2]);
                        $codigoestado2 = trim($datos[$estado2]);
                        $codigocp2 = trim($datos[$cp2]);
                        $codigonombre3 = trim($datos[$nombre3]);
                        $codigodireccion3 = trim($datos[$direccion3]);
                        $codigocolonia3 = trim($datos[$colonia3]);
                        $codigociudad3 = trim($datos[$ciudad3]);
                        $codigoestado3 = trim($datos[$estado3]);
                        $codigocp3 = trim($datos[$cp3]);
                        
                        $codigocanal = trim($datos[$canal]);
                        $codigopais = trim($datos[$pais]);
                        $codigovendedor = trim($datos[$vendedor]);
                        $arrvendedor = explode("_",$codigovendedor);
                        $codigovendedor = $arrvendedor[0]; 
                        $codigotipoindustria = trim($datos[$tipoindustria]);
                        $codigofechaalta = trim($datos[$fechaalta]);
                        
                        
                    # *****************************************************************************
                    # *** IMPRESION DE COLUMNAS 
                    # *****************************************************************************
                    # VALIDA QUE EL CODIGO FUE VALIDO; SI NO SOLO LO IGNORA.
                            if ($codigovalido==1) {
                                    echo "<tr>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=red>".$cont."</font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigoapaterno_razonsocial."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=Darkblue>".$codigoamaterno."</font>";
                                    echo "</td>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=Darkblue>".$codigonombre."</font>";
                                    echo "</td>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigorfc."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigodireccion."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigocolonia."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigociudad."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigoestado."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigocp."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigodirextra."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigotel."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigofax."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigoemail."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigolimitecredito."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigomoneda."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigocliente."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigoarea."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigodiascredito."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigotipocliente."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigonota."</b></font>";
                                    echo "</td>";
                                    
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigonombre2."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigodireccion2."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigocolonia2."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigociudad2."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigoestado2."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigocp2."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigonombre3."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigodireccion3."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigocolonia3."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigociudad3."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigoestado3."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigocp3."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigocanal."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigopais."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigovendedor."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigotipoindustria."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigofechaalta."</b></font>";
                                    echo "</td>";
                                    echo "</tr>";
                                    
                                    
                                    
                                    
                                    
                    # *****************************************************************************
                    # *** INSERCION DE LAS CUENTAS EN LA PRIMERA TABLA debtorsmaster
                    # *****************************************************************************
                        #nuevo cliente se le asigna un autonumerico mayor a cero
                        if ($_SESSION['AutoDebtorNo'] > 0) {
                            #identificador asignado por el sistema es autonumerico
                            if ($_SESSION['AutoDebtorNo']== 1) {
                                //$DebtorNo = GetNextTransNo(500, $db);
                                $DebtorNo = $codigocliente;
                            }
                        }
                        $BranchCode = substr($DebtorNo, 0, 10);
                        //echo "<br>debtorno: " . $DebtorNo;
                        if ($DebtorNo == '0'){
                        	$DebtorNo = GetNextTransNo(500, $db);
                        	$BranchCode = substr($DebtorNo, 0, 10);
                        	
                        	#verifica que no exista un grupo llamado igual en la base de datos
                        	$xapaterno = str_replace(' ','',str_replace('.','',(str_replace(',','',$datos[$apaterno_razonsocial]))));
                        	$xmaterno = str_replace(' ','',str_replace('.','',(str_replace(',','',$datos[$amaterno]))));
                        	$nombre = str_replace(' ','',str_replace('.','',(str_replace(',','',$datos[$nombre]))));
                        	
                        	$sqlq3 = "SELECT count(*)
                                FROM debtorsmaster
                                WHERE replace(replace(replace(name1,',',''),'.',''),' ','') = '" . strtoupper(trim(htmlspecialchars_decode($xapaterno,ENT_NOQUOTES))) . "'
                                and replace(replace(replace(name2,',',''),'.',''),' ','') = '" . strtoupper(trim(htmlspecialchars_decode($xmaterno,ENT_NOQUOTES))) . "'
                                and replace(replace(replace(name3,',',''),'.',''),' ','') = '" . strtoupper(trim(htmlspecialchars_decode($nombre,ENT_NOQUOTES))) . "'";
                        	$DbgMsg = _('El SQL que se utiliza para recuperar la información fue');
                        	$ErrMsg = _('No se puede comprobar si el grupo existe porque');
                        	$resultq3=DB_query($sqlq3, $db,$ErrMsg,$DbgMsg);
                        	$myrowq3=DB_fetch_row($resultq3);
                        	#si existe  manda mensaje de error
                        	if ($myrowq3[0]!=0) {
                        		$sqlq4 = "SELECT debtorno
	                                FROM debtorsmaster
	                                WHERE name1 = '" . strtoupper(trim(htmlspecialchars_decode($datos[$apaterno_razonsocial],ENT_NOQUOTES))) . "'
	                                and name2 = '" . strtoupper(trim(htmlspecialchars_decode($datos[$amaterno],ENT_NOQUOTES))) . "'
	                                and name3 = '" . strtoupper(trim(htmlspecialchars_decode($datos[$nombre],ENT_NOQUOTES))) . "'";
                        		$DbgMsg = _('El SQL que se utiliza para recuperar la información fue');
                        		$ErrMsg = _('No se puede comprobar si el grupo existe porque');
                        		$resultq4=DB_query($sqlq4, $db,$ErrMsg,$DbgMsg);
                        		$myrowq4=DB_fetch_row($resultq4);
                        		#si existe  manda mensaje de error
                        		$DebtorNo = $myrowq4[0];	
                        		$InputError = 1;
                        		$clienteexiste = 1;
                        	}
                        	
                        	
                        	
                        }
                        //echo "<br>debtorno2: " . $DebtorNo;
                        $sql = "
                        	INSERT INTO debtorsmaster (
								debtorno,
								name,
								name1,
								name2,
								name3,
								address1,
								address2,
								address3,
								address4,
								address5,
								address6,
								currcode,
	                            salestype,
								clientsince,
								holdreason,
								paymentterms,
								discount,
								invaddrbranch,
	                            discountcode,
								taxref,
								customerpoline,
								typeid,
								coments,
	                        	lastpaiddate
                			) VALUES (
                        		'" . $DebtorNo ."',
								'" . strtoupper(trim(htmlspecialchars_decode(str_replace(';',',',$codigoapaterno_razonsocial),ENT_NOQUOTES) . ' '.trim(htmlspecialchars_decode($codigoamaterno,ENT_NOQUOTES)) . ' '.trim(htmlspecialchars_decode($codigonombre,ENT_NOQUOTES)))) . "',
								'" . strtoupper(trim(htmlspecialchars_decode(str_replace(';',',',$codigoapaterno_razonsocial),ENT_NOQUOTES))) ."',
								'" . strtoupper(trim(htmlspecialchars_decode(str_replace(';',',',$codigoamaterno),ENT_NOQUOTES))) ."',
								'" . strtoupper(trim(htmlspecialchars_decode(str_replace(';',',',$codigonombre),ENT_NOQUOTES))) ."',
								'" . htmlspecialchars_decode(str_replace(';',',',$codigodireccion),ENT_NOQUOTES) ."',
								'" . htmlspecialchars_decode(str_replace(';',',',$codigocolonia),ENT_NOQUOTES) ."',
								'" . htmlspecialchars_decode(str_replace(';',',',$codigociudad),ENT_NOQUOTES) . "',
								'" . htmlspecialchars_decode(str_replace(';',',',$codigoestado),ENT_NOQUOTES) . "',
								'" . htmlspecialchars_decode($codigocp,ENT_NOQUOTES) . "',
								'" . htmlspecialchars_decode($codigodirextra,ENT_NOQUOTES) . "',
								'" . $codigomoneda . "',
                                'PL',
								'" . $codigofechaalta . "',
                                1,
                                '" . $codigodiascredito . "',
                                0,
                                0,
                                0,
                                '',
                                0,
                                '" . $codigotipocliente . "',
                                '',
								now()
							)";
                            //echo "<br>SQL: ". $sql;
                            //exit;
                        	$ErrMsg = _('Este cliente no fue ingresado a la base de datos por que');
                            if ($InputError == 0){
                        		$result = DB_query($sql,$db,$ErrMsg);
                            }
                            
                    # *****************************************************************************
                    # *** INSERCION DE LAS CUENTAS EN LA PRIMERA TABLA custbranch
                    # *****************************************************************************
                        #nuevo cliente se le asigna un autonumerico mayor a cero pero el mismo qu ese a insertado en debtorsmaster
                                    
                        $sql = "
                        	INSERT INTO custbranch (
                        		branchcode,
                                debtorno,
                                brname,
                                braddress1,
                                braddress2,
                                braddress3,
                                braddress4,
                                braddress5,
                                braddress6,
                                estdeliverydays,
                                fwddate,
                                phoneno,
                                faxno,
                                area,
                                email,
                                taxgroupid,
                                defaultlocation,
                                disabletrans,
                                defaultshipvia,
                                custbranchcode,
                                taxid,
                                brnumint,
                                brnumext,
                                salesman,
                        		paymentname,
                        		nocuenta,
                        		brpostaddr1,
                        		brpostaddr2,
                        		brpostaddr3,
                        		brpostaddr4,
                        		brpostaddr5,
                        		SectComClId,
                        		custPais,
                        		lineofbusiness
                        	) VALUES (
                        		'" . $BranchCode . "',
                                '" . $DebtorNo. "',
                                '" . strtoupper(trim(htmlspecialchars_decode(str_replace(';',',',$codigoapaterno_razonsocial),ENT_NOQUOTES) . ' '.trim(htmlspecialchars_decode($codigoamaterno,ENT_NOQUOTES)) . ' '.trim(htmlspecialchars_decode($codigonombre,ENT_NOQUOTES)))) . "',
                                '" . htmlspecialchars_decode(str_replace(';',',',$codigodireccion),ENT_NOQUOTES) . "',
                                '" . htmlspecialchars_decode(str_replace(';',',',$codigocolonia),ENT_NOQUOTES) . "',
                                '" . htmlspecialchars_decode(str_replace(';',',',$codigociudad),ENT_NOQUOTES) . "',
                                '" . htmlspecialchars_decode(str_replace(';',',',$codigoestado),ENT_NOQUOTES) . "',
                                '" . htmlspecialchars_decode($codigocp,ENT_NOQUOTES) . "',
                                '" . htmlspecialchars_decode($codigodirextra,ENT_NOQUOTES) . "',
                                0,
                                0,
                                '" . str_replace(';',',',$codigotel) . "',
                                '" . str_replace(';',',',$codigofax) . "',
                                '" . str_replace(';',',',$codigoarea) . "',
                                '" . str_replace(';',',',$codigoemail) . "',
                                1,
                                '$codigodefaultlocation',
                                '0',
                                '1',
                                '1',
                                '" . htmlspecialchars_decode($codigorfc,ENT_NOQUOTES) . "',
                                '" . str_replace(';',',',$codigonuminterno) . "',
                                '" . str_replace(';',',',$codigonumexterno) . "',
                                '" . $codigovendedor . "',
                                'No Identificado',
                                'No Identificado',
                                '" . htmlspecialchars_decode(str_replace(';',',',$codigodireccion2),ENT_NOQUOTES) . "',
                                '" . htmlspecialchars_decode(str_replace(';',',',$codigocolonia2),ENT_NOQUOTES) . "',
                                '" . htmlspecialchars_decode(str_replace(';',',',$codigociudad2),ENT_NOQUOTES) . "',
                                '" . htmlspecialchars_decode(str_replace(';',',',$codigoestado2),ENT_NOQUOTES) . "',
                                '" . htmlspecialchars_decode($codigocp2,ENT_NOQUOTES) . "',
                                '" . $codigocanal . "',
                                '" . $codigopais . "',
                                '" . $codigotipoindustria . "'
                                		
                            )";
							
                        	$ErrMsg = _('Los datos de la oficina del cliente no se insertaron por que');
                        	if ($InputError == 0){
								$result = DB_query($sql,$db,$ErrMsg);
                        	}
                       /*************SUCURSAL 2*********************/
                        	$BranchCode = $DebtorNo . "_A";
                        	if ($codigodireccion3 != ""){
	                        	$sql = "
	                        	INSERT INTO custbranch (
	                        		branchcode,
	                                debtorno,
	                                brname,
	                                braddress1,
	                                braddress2,
	                                braddress3,
	                                braddress4,
	                                braddress5,
	                                braddress6,
	                                estdeliverydays,
	                                fwddate,
	                                phoneno,
	                                faxno,
	                                area,
	                                email,
	                                taxgroupid,
	                                defaultlocation,
	                                disabletrans,
	                                defaultshipvia,
	                                custbranchcode,
	                                taxid,
	                                brnumint,
	                                brnumext,
	                                salesman,
	                        		paymentname,
	                        		nocuenta,
	                        		brpostaddr1,
	                        		brpostaddr2,
	                        		brpostaddr3,
	                        		brpostaddr4,
	                        		brpostaddr5,
	                        		SectComClId,
									custPais,
	                        		lineofbusiness
	                        	) VALUES (
	                        		'" . $BranchCode . "',
	                                '" . $DebtorNo. "',
	                                '" . strtoupper(trim(htmlspecialchars_decode(str_replace(';',',',$codigoapaterno_razonsocial),ENT_NOQUOTES) . ' '.trim(htmlspecialchars_decode($codigoamaterno,ENT_NOQUOTES)) . ' '.trim(htmlspecialchars_decode($codigonombre,ENT_NOQUOTES)))) . "',
	                                '" . htmlspecialchars_decode(str_replace(';',',',$codigodireccion3),ENT_NOQUOTES) . "',
	                                '" . htmlspecialchars_decode(str_replace(';',',',$codigocolonia3),ENT_NOQUOTES) . "',
	                                '" . htmlspecialchars_decode(str_replace(';',',',$codigociudad3),ENT_NOQUOTES) . "',
	                                '" . htmlspecialchars_decode(str_replace(';',',',$codigoestado3),ENT_NOQUOTES) . "',
	                                '" . htmlspecialchars_decode($codigocp3,ENT_NOQUOTES) . "',
	                                '" . htmlspecialchars_decode($codigodirextra3,ENT_NOQUOTES) . "',
	                                0,
	                                0,
	                                '" . str_replace(';',',',$codigotel) . "',
	                                '" . str_replace(';',',',$codigofax) . "',
	                                '" . str_replace(';',',',$codigoarea) . "',
	                                '" . str_replace(';',',',$codigoemail) . "',
	                        	                                1,
	                        	                                '$codigodefaultlocation',
	                        	                                '0',
	                        	                                '1',
	                        	                                '1',
	                        	                                '" . htmlspecialchars_decode($codigorfc,ENT_NOQUOTES) . "',
	                                '" . str_replace(';',',',$codigonuminterno) . "',
	                                '" . str_replace(';',',',$codigonumexterno) . "',
	                                '" . $codigovendedor . "',
	                                'No Identificado',
	                                'No Identificado',
	                                '" . htmlspecialchars_decode(str_replace(';',',',$codigodireccion3),ENT_NOQUOTES) . "',
	                                '" . htmlspecialchars_decode(str_replace(';',',',$codigocolonia3),ENT_NOQUOTES) . "',
	                                '" . htmlspecialchars_decode(str_replace(';',',',$codigociudad3),ENT_NOQUOTES) . "',
	                                '" . htmlspecialchars_decode(str_replace(';',',',$codigoestado3),ENT_NOQUOTES) . "',
	                                '" . htmlspecialchars_decode($codigocp3,ENT_NOQUOTES) . "',
	                                '" . $codigocanal . "',
									'" . $codigopais . "',
									'" . $codigotipoindustria . "'
											
	                                		
	                            )";
	                        		
	                        	$ErrMsg = _('Los datos de la oficina del cliente no se insertaron por que');
	                        	if ($InputError == 0){
	                        		$result = DB_query($sql,$db,$ErrMsg);
	                        	}
                        	}
                       /********************************************/ 	
                        	
						/*************SUCURSAL ADICIONAL*********************/
	                    if ($clienteexiste == 1){
	                    		$sqlq = "SELECT count(*)
                                FROM custbranch
                                WHERE debtorno = '" . trim($DebtorNo) . "'
	                    			and braddress1 = '" . trim($codigodireccion3). "'";
		                    	//echo $sqlq;
		                    	//exit;
		                    	$DbgMsg = _('El SQL que se utiliza para recuperar la información fue');
		                    	$ErrMsg = _('No se puede comprobar si el grupo existe porque');
		                    	$resultq=DB_query($sqlq, $db,$ErrMsg,$DbgMsg);
		                    	$myrowq=DB_fetch_row($resultq);
		                    	#si existe  manda mensaje de error
		                    	if ($myrowq[0]==0) {
		                    		
		                    		$sqlq2 = "SELECT count(*)
                                		FROM custbranch
                                		WHERE debtorno = '" . trim($DebtorNo) . "'";
		                    		$DbgMsg = _('El SQL que se utiliza para recuperar la información fue');
		                    		$ErrMsg = _('No se puede comprobar si el grupo existe porque');
		                    		$resultq2=DB_query($sqlq2, $db,$ErrMsg,$DbgMsg);
		                    		$myrowq2=DB_fetch_row($resultq2);
		                    		#si existe  manda mensaje de error
		                    		switch($myrowq2[0]) {
		                    			case 1:
		                    				$BranchCode = $DebtorNo . "_A";
		                    				break;
		                    			case 2:
		                    				$BranchCode = $DebtorNo . "_B";
		                    				break;
		                    			case 3:
		                    				$BranchCode = $DebtorNo . "_C";
		                    				break;
		                    			case 4:
		                    				$BranchCode = $DebtorNo . "_D";
		                    				break;
		                    			case 5:
		                    				$BranchCode = $DebtorNo . "_E";
		                    				break;
		                    			case 6:
		                    				$BranchCode = $DebtorNo . "_F";
		                    				break;
	                    				case 7:
	                    					$BranchCode = $DebtorNo . "_G";
	                    					break;
                    					case 8:
                    						$BranchCode = $DebtorNo . "_H";
                    						break;
                    					case 9:
                    						$BranchCode = $DebtorNo . "_I";
                    						break;
                    					case 10:
                    						$BranchCode = $DebtorNo . "_J";
                    						break;
		                    							
                    					case 11:
                    						$BranchCode = $DebtorNo . "_K";
                    						break;
                    					case 12:
                    						$BranchCode = $DebtorNo . "_L";
                    						break;
                    					case 13:
                    						$BranchCode = $DebtorNo . "_M";
                    						break;
                    					case 14:
                    						$BranchCode = $DebtorNo . "_N";
                    						break;
                    					case 15:
                    						$BranchCode = $DebtorNo . "_O";
                    						break;
                    					case 16:
                    						$BranchCode = $DebtorNo . "_P";
                    						break;
                    					case 17:
                    						$BranchCode = $DebtorNo . "_Q";
                    						break;
                    					case 18:
                    						$BranchCode = $DebtorNo . "_R";
                    						break;
                    					case 19:
                    						$BranchCode = $DebtorNo . "_S";
                    						break;
                    					case 20:
                    						$BranchCode = $DebtorNo . "_T";
                    						break;
                    					case 21:
                    						$BranchCode = $DebtorNo . "_U";
                    						break;
                    					case 22:
                    						$BranchCode = $DebtorNo . "_V";
                    						break;
                    					case 23:
                    						$BranchCode = $DebtorNo . "_W";
                    						break;
                    					case 24:
                    						$BranchCode = $DebtorNo . "_X";
                    						break;
                    					case 25:
                    						$BranchCode = $DebtorNo . "_Y";
                    						break;
                    					case 26:
                    						$BranchCode = $DebtorNo . "_Z";
                    						break;
                    								
		                    			default:
		                    				$BranchCode = $DebtorNo . "_AA";
		                    				
		                    		}
		                    		
		                    		
		                    		
			                    		$sql = "
		                        	INSERT INTO custbranch (
		                        		branchcode,
		                                debtorno,
		                                brname,
		                                braddress1,
		                                braddress6,
		                                braddress2,
		                                braddress3,
		                                braddress4,
		                                braddress5,
		                                estdeliverydays,
		                                fwddate,
		                                phoneno,
		                                faxno,
		                                area,
		                                email,
		                                taxgroupid,
		                                defaultlocation,
		                                disabletrans,
		                                defaultshipvia,
		                                custbranchcode,
		                                taxid,
		                                brnumint,
		                                brnumext,
		                                salesman,
		                        		paymentname,
		                        		nocuenta,
		                        		brpostaddr1,
		                        		brpostaddr2,
		                        		brpostaddr3,
		                        		brpostaddr4,
		                        		brpostaddr5,
			                    		SectComClId,
										custPais,
			                    		lineofbusiness
		                        	) VALUES (
		                        		'" . $BranchCode . "',
		                                '" . $DebtorNo. "',
		                                '" . strtoupper(trim(htmlspecialchars_decode(str_replace(';',',',$codigoapaterno_razonsocial),ENT_NOQUOTES) . ' '.trim(htmlspecialchars_decode($codigoamaterno,ENT_NOQUOTES)) . ' '.trim(htmlspecialchars_decode($codigonombre,ENT_NOQUOTES)))) . "',
		                                '" . htmlspecialchars_decode(str_replace(';',',',$codigodireccion),ENT_NOQUOTES) . "',
		                                '" . htmlspecialchars_decode(str_replace(';',',',$codigocolonia),ENT_NOQUOTES) . "',
		                                '" . htmlspecialchars_decode(str_replace(';',',',$codigociudad),ENT_NOQUOTES) . "',
		                                '" . htmlspecialchars_decode(str_replace(';',',',$codigoestado),ENT_NOQUOTES) . "',
		                                '" . htmlspecialchars_decode($codigocp,ENT_NOQUOTES) . "',
		                                '" . htmlspecialchars_decode($codigodirextra,ENT_NOQUOTES) . "',
		                                0,
		                                0,
		                                '" . str_replace(';',',',$codigotel) . "',
		                                '" . str_replace(';',',',$codigofax) . "',
		                                '" . str_replace(';',',',$codigoarea) . "',
		                                '" . str_replace(';',',',$codigoemail) . "',
			                    		1,
			                    		'$codigodefaultlocation',
			                    		'0',
			                    		'1',
			                    		'1',
			                    		'" . htmlspecialchars_decode($codigorfc,ENT_NOQUOTES) . "',
			                    		'" . str_replace(';',',',$codigonuminterno) . "',
			                    		'" . str_replace(';',',',$codigonumexterno) . "',
			                    		'" . $codigovendedor . "',
			                    		'No Identificado2xx',
			                    		'No Identificado2xx',
			                    		'" . htmlspecialchars_decode(str_replace(';',',',$codigodireccion2),ENT_NOQUOTES) . "',
			                       	     '" . htmlspecialchars_decode(str_replace(';',',',$codigocolonia2),ENT_NOQUOTES) . "',
			                    		'" . htmlspecialchars_decode(str_replace(';',',',$codigociudad2),ENT_NOQUOTES) . "',
			                    		'" . htmlspecialchars_decode(str_replace(';',',',$codigoestado2),ENT_NOQUOTES) . "',
										'" . htmlspecialchars_decode($codigocp2,ENT_NOQUOTES) . "',
			                    			 '" . $codigocanal . "',
										'" . $codigopais . "',
										'" . $codigotipoindustria . "'
												
												
			                    		)";
			                    			                       	                                						 
			                   			$ErrMsg = _('Los datos de la oficina del cliente no se insertaron por que');
			            
			                    		$result = DB_query($sql,$db,$ErrMsg);
			                    	}

	                    } 	
                       //***************************************
                        	
                        	
							# *****************************************************************************
							# *** INSERCION DE CORREO EN CUSTMAILS
							# *****************************************************************************
							
							if(empty($codigoemail) == FALSE) {
								
								$codigoemail = str_replace("/",",",str_replace(';', ',', $codigoemail));
								$arremail = explode(',',$codigoemail);
								for($ii=0; $ii<count($arremail);$ii++){
									$sql = "
		                        		INSERT INTO custmails (
		                               	    debtorno,
		                               	    branchcode,
		                                    email,
		                                    trandate,
		                                    active
		                        	    ) VALUES (
		                                    '" . $DebtorNo. "',
		                                    '" . $BranchCode . "',
		                                    '" . $arremail[$ii] . "',
		                                    NOW(),
		                                    1
		                            	)";
									
									$ErrMsg = _('Los datos del correo del cliente no se insertaron por que');
									if ($InputError == 0){
										$result = DB_query($sql,$db,$ErrMsg);
									}
								}
							}
							
							# *****************************************************************************
							# *** INSERCION CATALOGO DIASXCLIENTE
							# *****************************************************************************
							
							$sql = "
                        		INSERT INTO diasxcliente (
                               	    id_usuario,
                               	    id_depto,
                                    id_cliente,
                                   	limitecredit,
                                    numdias
                        	    ) VALUES (
									'" . $_SESSION['UserID'] . "',
									'1',
                                    '" . $DebtorNo. "',
                                    '" . $codigolimitecredito . "',
                                    '" . $codigodiascreditotmp . "'
                            	)
                            ";
							
							$ErrMsg = _('Los datos del cliente no se insertaron en catálogo diasxcliente por que');
							if ($InputError == 0){
								$result = DB_query($sql,$db,$ErrMsg);
							}
							
							# *****************************************************************************
							# *** INSERCION NOTAS
							# *****************************************************************************
							if(empty($codigonota) == FALSE) {	
								$sql = "
	                        		INSERT INTO custnotes (
	                               	    noteid, 
										debtorno,
										href,
										note,
										date,
										priority
	                        	    ) VALUES (
										NULL,
										'" . $DebtorNo . "',
										'',
	                                    '" . $codigonota. "',
	                                    Now(),
	                                    '1'
	                            	)
	                            ";
									
								$ErrMsg = _('Los datos del cliente no se insertaron en catálogo diasxcliente por que');
								if ($InputError == 0){
									$result = DB_query($sql,$db,$ErrMsg);
								}
							}
							
                            $cont = $cont + 1;
                            $k=$k+1;
                        };
                    };
                }; # Fin condicion columnas < mincolumnas    
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
        echo "<font size=2 color=Darkblue><b>"._('CARGAR CLIENTES')."</b></font>";
      echo "</td>";
    echo "</tr>";
    
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo "<font size=2 color=Darkblue><b>"._('ESTE ES EL FORMATO DEL ARCHIVO A SUBIR')."</b></font>";
        echo "<br><br>";
      echo "</td>";
    echo "</tr>";
    
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo '<table cellpadding=2>';
          echo '<tr>';
            echo '<td style="text-align:center;"><font size=1>A.Paterno/<br>Razon Social</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>A.Materno</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Nombre(s)</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>RFC</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Direccion</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Colonia</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Ciudad</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Estado</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>CP</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Direccion Extra</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Telefono</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Fax</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Email</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Limite de credito</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Moneda <br/>MXN = Pesos Mexicanos <br/>USD = Dólares</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Clave Cliente</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Clave Area</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Dias Credito</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>TipoCliente</font></td>';
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