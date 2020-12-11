<?php
/*
CGM 14/01/2014 Se genero version 6.0 permite eliminar movimientos desde un rango de fechas, al eliminar solo elimina movimientos no conciliados, agrega la fecha de conciliacion con bancos
*/
$PageSecurity = 5;
include('includes/session.inc');
$title = _('Cargar Estados de Cuenta Bancarios');
include('includes/header.inc');
$funcion=501;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['ToYear'])) {
        $ToYear=$_POST['ToYear'];
} else {
        $ToYear=date('Y');
}

if (isset($_POST['ToMes'])) {
        $ToMes=$_POST['ToMes'];
} else {
        $ToMes=date('m');
}
    
if (isset($_POST['ToDia'])) {
    $ToDia=$_POST['ToDia'];
} else {
    $ToDia=date('d');
}

$fechafin= rtrim($ToYear). '-'.rtrim($ToMes).'-'.rtrim($ToDia);
$fechafinc=mktime(23,59,59,rtrim($ToMes),rtrim($ToDia),rtrim($ToYear));
$fechafin= rtrim($ToYear).'-'.add_ceros(rtrim($ToMes),2).'-'.add_ceros(rtrim($ToDia),1) . ' 23:59:59';
 
$InputError = 0;

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

if (isset($_POST['area']))
{
    $area = $_POST['area'];
}

echo $area;


if (isset($_POST['cargar']) and $separador<>'')
{

    $nombre_archivo = $_FILES['userfile']['name']; 
    $tipo_archivo = $_FILES['userfile']['type']; 
    $tamano_archivo = $_FILES['userfile']['size']; 

    $filename = 'pricelists/'.$nombre_archivo;

    # ************************************************************************
    # Elimina la informacion de la tabla de estado de cuenta primero
    # ************************************************************************
    
    /*
        UPDATE banktrans 
                set banktrans.batchconciliacion = 0,
                    banktrans.amountcleared = banktrans.amount,
                    banktrans.usuario = 'INIT-ADMIN'
        WHERE banktrans.transdate <= '2011/04/30'
    */
    
    $sSQL = "UPDATE banktrans LEFT JOIN estadoscuentabancarios ON banktrans.batchconciliacion = estadoscuentabancarios.batchconciliacion
                        set banktrans.batchconciliacion = null,
                            banktrans.amountcleared = 0,
                            banktrans.usuario = null
                WHERE   banktrans.batchconciliacion > 0 AND
                        banktrans.bankact = '". $_POST['BankAccount'] ."' AND
                        estadoscuentabancarios.batchconciliacion is null";
          
   // $result = DB_query($sSQL,$db);
    
    $sSQL = "UPDATE estadoscuentabancarios LEFT JOIN banktrans ON banktrans.batchconciliacion = estadoscuentabancarios.batchconciliacion
                        set estadoscuentabancarios.batchconciliacion = null,
                            estadoscuentabancarios.conciliado = 0,
                            estadoscuentabancarios.usuario = null
                WHERE   estadoscuentabancarios.batchconciliacion > 0 AND
                        estadoscuentabancarios.cuenta = '". $_POST['BankAccount'] ."' AND
                        banktrans.batchconciliacion is null";
          
    //$result = DB_query($sSQL,$db);
    
    if (isset($_POST['eliminarprimero'])) {
    	
         $sSQL = "UPDATE banktrans 
         			JOIN estadoscuentabancarios ON banktrans.batchconciliacion = estadoscuentabancarios.batchconciliacion
                          set banktrans.batchconciliacion = null,
                              banktrans.amountcleared = 0,
                              banktrans.usuario = null
                  WHERE banktrans.bankact = '". $_POST['BankAccount'] ."' AND
                        banktrans.batchconciliacion <> 0 AND
                              ( cuenta is null OR (cuenta='". $_POST['BankAccount'] ."' AND
                              				  (DAY(Fecha) >= ". $_POST['ToDia']. " AND
                                              MONTH(Fecha) = ". $_POST['ToMes']. " AND
                                              YEAR(Fecha) = ". $_POST['ToYear'].") ) )";
         // echo '<pre><br>sql:'.$sSQL;
          $result = DB_query($sSQL,$db);
         
          
         $sSQL= "DELETE FROM estadoscuentabancarios";
         $sSQL.= " WHERE cuenta='". $_POST['BankAccount'] ."' AND
                          ((MONTH(Fecha) = ". $_POST['ToMes']. " AND
                           DAY(Fecha) >= ". $_POST['ToDia']. " AND
                          YEAR(Fecha) = ". $_POST['ToYear'].") OR MONTH(Fecha) = 0 OR YEAR(Fecha) < 1990)";
                          
          
         $result = DB_query($sSQL,$db);
    }
    # ************************************************************************
     
    if ($tipo_archivo=='text/csv' OR $tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='application/octet-stream')
    {
        
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename)){
            
            
          
            
        
        # UNA VEZ QUE EL ARCHIVO SE CARGO, ENTRA A LEER EL CONTENIDO
        
        #Declara variables
        
        $tieneerrores=0;
        $lineatitulo=0;
        $mincolumnas=4;
        
        $columnafecha=0;
        $columnaconcepto=1;
        $columnaretiro=2;
        $columnadeposito=3;
        
        # ABRE ERL ARCHIVO Y LO ALMACENA EN UN OBJETO    
        $lineas = file($filename);
            
            
            # ****************************
            # **** RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO ****
            # ****************************
           
           
            $registrosMATCH1 = 0;    
            $registrosMATCH2 = 0;
            $registrosMULTIMATCH1 = 0;
            $registrosNOMATCH1 = 0;
            $totalRegistrosInsertados = $totalRegistrosInsertados + 1;
            
            $cont=0;
            foreach ($lineas as $line_num => $line)
              {
                /**REEMPLAZA CARACTERES NO VALIDOS*/
                $line =str_replace("'","",$line); //QUITA COMILLA SIMPLE
                $line =str_replace('"','',$line); //QUITA DOBLE COMILLA
                
                $datos = explode($separador, $line); # Convierte en array cada una de las lineas
                $columnaslinea = count($datos);           # Obtiene el numero de columnas de la linea en base al separador
                
                # ****************************
                # **** PRIMERA VALIDACION ****
                # **** columnas de la linea menores que la minimas requeridas?***
                # ****************************
                if ($columnaslinea<$mincolumnas)
                {
                  $tieneerrores = 1;
                  $error = _('EL NUMERO MINIMO DE COLUMNAS REQUERIDAS NO SE CUMPLE EN LA LINEA NO.. ') . intval($line_num+1);
                  $error .= '<br>'. _('La estructura del archivo debe de tener al menos '.$mincolumnas.' datos separados por "'.$separador.'"' );
                  prnMsg($error,'error');
                  exit;
                }    
                else
                {
                        # ********************************
                        # *** RECORRE LAS LINEAS DE DATOS
                        # ********************************
                        
                        # *****************************************************************************                            
                        # *** COLUMNA FECHA ***
                        # *****************************************************************************
                        
                        $codigofecha = trim($datos[$columnafecha]);
                        
                        if ($codigofecha != '')
                        {
                            if (SUBSTR($codigofecha,2,1) == '/' or SUBSTR($codigofecha,2,1) == '-') {
                                /* VIENE COMO MES DIA A„O  o DIA MES A„O */
                                
                                if ($ToMes*1 == SUBSTR($codigofecha,3,2)*1) {
                                    /* Los primeros segundos dos digitos de esta linea son iguales al mes en que se va a cargar el estado de cuenta,
                                        la mayor probabilidad es que el formato venga en DIA/MES/A„O */
                                    $fechatrans = SUBSTR($codigofecha,6,4).'/'.SUBSTR($codigofecha,3,2).'/'.SUBSTR($codigofecha,0,2);    
                                } else {
                                    /* Debe de ser formato MES/DIA/A„O */
                                    $fechatrans = SUBSTR($codigofecha,6,4).'/'.SUBSTR($codigofecha,0,2).'/'.SUBSTR($codigofecha,3,2);    
                                }
                                
                            } elseif (SUBSTR($codigofecha,4,1) == '/' or SUBSTR($codigofecha,4,1) == '-') {
                                /* VIENE COMO A„O MES DIA */
                                $fechatrans = SUBSTR($codigofecha,0,4).'/'.SUBSTR($codigofecha,5,2).'/'.SUBSTR($codigofecha,8,2);
                            }
                              
                               $codigoretiro='';
                          
                          if (is_numeric(trim($datos[$columnaretiro])))
                          {
                              $codigoretiro = trim($datos[$columnaretiro])*1;
                          } else {
                              $codigoretiro = 0;
                          }; # Fin de if trae codigo de cliente
                          
                          
                          $codigodeposito='';
                          
                          if (is_numeric(trim($datos[$columnadeposito])))
                          {
                              $codigodeposito = trim($datos[$columnadeposito])*1;
                          } else {
                            $codigodeposito = 0;
                          }; # Fin de if trae codigo de cliente
                          
                              
                            # Elimina el registro que ya exista e la tabla prices
                            
                            # Inserta registro en la tabla prices
                            
                            $sSQL= "INSERT INTO estadoscuentabancarios (Fecha, Concepto, cuenta, Retiros, depositos, usuario)
                                        VALUES('".$fechatrans."','".str_replace('"','', $datos[$columnaconcepto])."','".$_POST['BankAccount']."','".$codigoretiro."','".$codigodeposito."','".$_SESSION['UserID']."')";
                            
                            
                            
                            //echo $sSQL."<br>D:".$datos[$columnadeposito]."<BR>";
                            if ((abs($codigoretiro)+abs($codigodeposito)) > 0) {
                                
                                $result = DB_query($sSQL,$db);
                                
                                $totalRegistrosInsertados = $totalRegistrosInsertados + 1;
                                
                                $esteid = $_SESSION['LastInsertId'];
                                /* AQUI VOY A PONER LA LOGICA PARA QUE AUTOMATICAMENTE CHEQUE MOVIMIENTOS QUE HACEN MATCH UNICO Y EXACTO !!! */
                                
                                
                                
                                if ($codigoretiro > 0) {
                                    $ssql = "SELECT count(*) as unico,
                                                    max(banktransid) as banktransid,
                                                    max(tagref) as tagref,transdate
                                            FROM banktrans 
                                            WHERE amount = ".($codigoretiro*-1)."
                                                    AND transdate <= '" . $fechatrans . "'
                                                    AND bankact='" .$_POST["BankAccount"] . "'
                                                    AND  amountcleared = 0";
                                    
                                    $result = DB_query($ssql,$db);
                                    if ($myrow=DB_fetch_array($result)) {
                                        if ($myrow['unico'] == 1) {
                                            
                                            //prnMsg(_('ENCONTRE MATCH UNICO ! ID:'). $myrow['banktransid'] ,'success');
                                            //prnMsg($line ,'success');
                                            
                                            $registrosMATCH1 = $registrosMATCH1 + 1;
                                
                                            $TransNo = GetNextTransNo(600, $db);
		
                                            $sql = "SELECT periodno, lastdate_in_period
                                                            FROM periods
                                                            WHERE YEAR(lastdate_in_period) = ". $ToYear . " AND MONTH(lastdate_in_period) = ". $ToMes;
                                                            
                                            $ErrMsg =  _('Could not retrieve transaction information');
                                            $resultCC = DB_query($sql,$db,$ErrMsg);
                                            $myrowCC=DB_fetch_array($resultCC);
                                            $periododeMatch = $myrowCC[0];
                                            
                                            $sql = "UPDATE banktrans SET amountcleared= ". ($codigoretiro*-1) .
                                                            ",usuario = '".$_SESSION['UserID']."-AUT',
                                                            fechacambio = NOW(),
                                                            fechabanco='".$fechatrans."',
                                                            batchconciliacion= ".$TransNo.",
                                                            matchperiodno = ".$periododeMatch." WHERE banktransid=" . $myrow['banktransid'];
                                            $ErrMsg =  _('Could not match off this payment because');
                                            $resultCC = DB_query($sql,$db,$ErrMsg);
                                            
                                            $sql = "UPDATE estadoscuentabancarios SET conciliado= ". ($codigoretiro) . ",
                                                            usuario = '".$_SESSION['UserID']."-AUT', fechacambio = NOW(),
                                                            tagref = '".$myrow['tagref']."',
                                                            fechacontable='".$myrow['transdate']."',
                                                            batchconciliacion= ".$TransNo."
                                                             WHERE banktransid=" . $esteid;
                                            $ErrMsg =  _('Could not match off this payment because');
                                            
                                            //echo $sql;
                                            $resultCC = DB_query($sql,$db,$ErrMsg);
                                            
                                        } elseif ($myrow['unico'] > 1) {
                                            //prnMsg(_('ENCONTRE MULTIPLES MATCH ! NUMERO:'). $myrow['unico'] ,'warn');
                                            //prnMsg($line ,'warn');
                                            /* CON MULTIPLES MATCH */
                                            
                                            /* INTENTA BUSCAR REGISTROS DENTRO DE LOS DIEZ DIAS MAS CERCANOS !! */
                                            $registrosMULTIMATCH1 = $registrosMULTIMATCH1 + 1;
                                            
                                            $numeroAutorizacion = trim($datos[$columnaconcepto]);
                                            $numeroAutorizacion = str_replace("."," ",$numeroAutorizacion);
                                            $numeroAutorizacion = str_replace("-"," ",$numeroAutorizacion);
                                            $numeroAutorizacion = str_replace("/"," ",$numeroAutorizacion);
                                            $numeroAutorizacion = str_replace("&"," ",$numeroAutorizacion);
                                            
                                            /* EXPLOTA CADA PARTE SEPARADA POR ESPACIOS EN UN ELEMENTO DEL ARREGLO */
                                            
                                            $arregloDePalabras = explode(" ",$numeroAutorizacion);
                                            
                                            
                                            $likeStr = "";
                                            $sientro = 0;
                                            for ($rept = 0; $rept < count($arregloDePalabras); $rept++ ) {
                                                if (is_numeric($arregloDePalabras[$rept])) {
                                                    if (strlen($arregloDePalabras[$rept]) >= 4) {
                                                        $sientro = $sientro + 1;
                                                        
                                                        if ($sientro > 1)
                                                            $likeStr = $likeStr . " OR ref like '%" . $arregloDePalabras[$rept]."%'";
                                                        else
                                                            $likeStr = $likeStr . "ref like '%" . $arregloDePalabras[$rept]."%'";
                                                    }
                                                }
                                            }
                                            
                                            if ($sientro > 0) {
                                                $likeStr = "AND (".$likeStr;
                                                $likeStr = $likeStr . ")";    
                                            }
                                            
                                            $ssql = "SELECT count(*) as unico,
                                                            max(banktransid) as banktransid,
                                                            max(tagref) as tagref,transdate
                                                    FROM banktrans 
                                                    WHERE amount = ".($codigoretiro*-1)."
                                                            AND transdate <= '" . $fechatrans . "'
                                                            AND transdate >= '" . $fechatrans . "'
                                                            AND bankact='" .$_POST["BankAccount"] . "'
                                                            AND  amountcleared = 0 ".$likeStr;
                                            
                                            $result = DB_query($ssql,$db);
                                            if ($myrow=DB_fetch_array($result)) {
                                                if ($myrow['unico'] == 1) {
                                                    prnMsg(_('ENCONTRE MATCH UNICO **** 2 INTENTO ***** ! ID:'). $myrow['banktransid'] ,'success');
                                                    prnMsg($likeStr ,'success');
                                                    prnMsg($line ,'success');
                                                    
                                                    $registrosMATCH2 = $registrosMATCH2 + 1;
                                                    $registrosMULTIMATCH1 = $registrosMULTIMATCH1 - 1;
                                                    $TransNo = GetNextTransNo(600, $db);
		
                                                    $sql = "SELECT periodno, lastdate_in_period
                                                                    FROM periods
                                                                    WHERE YEAR(lastdate_in_period) = ". $ToYear . " AND MONTH(lastdate_in_period) = ". $ToMes;
                                                                    
                                                    $ErrMsg =  _('Could not retrieve transaction information');
                                                    $resultCC = DB_query($sql,$db,$ErrMsg);
                                                    $myrowCC=DB_fetch_array($resultCC);
                                                    $periododeMatch = $myrowCC[0];
                                                    
                                                    $sql = "UPDATE banktrans SET amountcleared= ". ($codigoretiro*-1) .
                                                                    ",usuario = '".$_SESSION['UserID']."-AUT',
                                                                    fechacambio = NOW(),
                                                                     fechabanco='".$fechatrans."',
                                                                    batchconciliacion= ".$TransNo.",
                                                                    matchperiodno = ".$periododeMatch." WHERE banktransid=" . $myrow['banktransid'];
                                                    $ErrMsg =  _('Could not match off this payment because');
                                                    $resultCC = DB_query($sql,$db,$ErrMsg);
                                                    
                                                    $sql = "UPDATE estadoscuentabancarios SET conciliado= ". ($codigoretiro) . ",
                                                                    usuario = '".$_SESSION['UserID']."-AUT', fechacambio = NOW(),
                                                                    tagref = '".$myrow['tagref']."',
                                                                    batchconciliacion= ".$TransNo."
                                                                     WHERE banktransid=" . $esteid;
                                                    $ErrMsg =  _('Could not match off this payment because');
                                                    
                                                    //echo $sql;
                                                    $resultCC = DB_query($sql,$db,$ErrMsg);
                                                }
                                            }
                                            
                                        } else {
                                            //prnMsg(_('SIN MATCH !') ,'error');
                                            //prnMsg($line ,'error');
                                           /* SIN MATCH ALGUNO !! */
                                           
                                           $registrosNOMATCH1 = $registrosNOMATCH1 + 1;
                                        }
                                        
                                    } 
                                    
                                } elseif ($codigodeposito > 0) {
                                    $ssql = "SELECT count(*) as unico,
                                                    max(banktransid) as banktransid,
                                                    max(tagref) as tagref,transdate
                                            FROM banktrans 
                                            WHERE amount = ".($codigodeposito)."
                                                    AND transdate <= '" . $fechatrans . "'
                                                    AND bankact='" .$_POST["BankAccount"] . "'
                                                    AND  amountcleared = 0";
                                    
                                    $result = DB_query($ssql,$db);
                                    if ($myrow=DB_fetch_array($result)) {
                                        if ($myrow['unico'] == 1) {
                                            
                                            //prnMsg(_('ENCONTRE MATCH UNICO ! ID:'). $myrow['banktransid'] ,'success');
                                            //prnMsg($line ,'success');
                                            
                                            $registrosMATCH1 = $registrosMATCH1 + 1;
                                            
                                            $TransNo = GetNextTransNo(600, $db);
		
                                            $sql = "SELECT periodno, lastdate_in_period
                                                            FROM periods
                                                            WHERE YEAR(lastdate_in_period) = ". $ToYear . " AND MONTH(lastdate_in_period) = ". $ToMes;
                                                            
                                            $ErrMsg =  _('Could not retrieve transaction information');
                                            $resultCC = DB_query($sql,$db,$ErrMsg);
                                            $myrowCC=DB_fetch_array($resultCC);
                                            $periododeMatch = $myrowCC[0];
                                            
                                            $sql = "UPDATE banktrans 
                                            		SET amountcleared= ". ($codigodeposito) .",
                                                        usuario = '".$_SESSION['UserID']."-AUT',
                                                        fechacambio = NOW(),
                                                        fechabanco='".$fechatrans."',
                                                        batchconciliacion= ".$TransNo.",
                                                        matchperiodno = ".$periododeMatch." 
                                                     WHERE banktransid=" . $myrow['banktransid'];
                                            $ErrMsg =  _('Could not match off this payment because');
                                            $resultCC = DB_query($sql,$db,$ErrMsg);
                                            
                                            $sql = "UPDATE estadoscuentabancarios 
                                            		SET conciliado= ". ($codigodeposito*-1) . ",
                                                        usuario = '".$_SESSION['UserID']."-AUT', fechacambio = NOW(),
                                                        tagref = '".$myrow['tagref']."',
                                                        fechacontable='".$myrow['transdate']."',
                                                        batchconciliacion= ".$TransNo."
                                                    WHERE banktransid=" . $esteid;
                                            $ErrMsg =  _('Could not match off this payment because');
                                            
                                            //echo $sql;
                                            $resultCC = DB_query($sql,$db,$ErrMsg);
                                            
                                            
                                        } elseif ($myrow['unico'] > 1) {
                                            //prnMsg(_('ENCONTRE MULTIPLES MATCH ! NUMERO:'). $myrow['unico'] ,'warn');
                                            //prnMsg($line ,'warn');
                                            /* CON MULTIPLES MATCH */
                                            
                                            
                                            $registrosMULTIMATCH1 = $registrosMULTIMATCH1 + 1;
                                            
                                            $ssql = "SELECT count(*) as unico,
                                                    max(banktransid) as banktransid,
                                                    max(tagref) as tagref,transdate
                                            FROM banktrans 
                                            WHERE amount = ".($codigodeposito)."
                                                    AND transdate <= '" . $fechatrans . "'
                                                    AND transdate >= DATE_SUB('" . $fechatrans . "', INTERVAL 10 DAY)
                                                    AND bankact='" .$_POST["BankAccount"] . "'
                                                    AND  amountcleared = 0";
                                            
                                            $result = DB_query($ssql,$db);
                                            if ($myrow=DB_fetch_array($result)) {
                                                if ($myrow['unico'] == 1) {
                                                    //prnMsg(_('ENCONTRE MATCH UNICO **** 2 INTENTO ***** ! ID:'). $myrow['banktransid'] ,'success');
                                                    //prnMsg($line ,'success');
                                                    
                                                    $registrosMATCH2 = $registrosMATCH2 + 1;
                                                    $registrosMULTIMATCH1 = $registrosMULTIMATCH1 - 1;
                                                    $TransNo = GetNextTransNo(600, $db);
		
                                                    $sql = "SELECT periodno, lastdate_in_period
                                                                    FROM periods
                                                                    WHERE YEAR(lastdate_in_period) = ". $ToYear . " AND MONTH(lastdate_in_period) = ". $ToMes;
                                                                    
                                                    $ErrMsg =  _('Could not retrieve transaction information');
                                                    $resultCC = DB_query($sql,$db,$ErrMsg);
                                                    $myrowCC=DB_fetch_array($resultCC);
                                                    $periododeMatch = $myrowCC[0];
                                                    
                                                    $sql = "UPDATE banktrans SET amountcleared= ". ($codigodeposito) .",
                                                    				usuario = '".$_SESSION['UserID']."-AUT',
                                                                    fechacambio = NOW(),
                                                                    batchconciliacion= ".$TransNo.",
                                                                    fechabanco='".$fechatrans."',
                                                                    matchperiodno = ".$periododeMatch." 
                                                             WHERE banktransid=" . $myrow['banktransid'];
                                                    $ErrMsg =  _('Could not match off this payment because');
                                                    $resultCC = DB_query($sql,$db,$ErrMsg);
                                                    
                                                    $sql = "UPDATE estadoscuentabancarios 
                                                    		SET conciliado= ". ($codigodeposito*-1) . ",
                                                                    usuario = '".$_SESSION['UserID']."-AUT', fechacambio = NOW(),
                                                                    tagref = '".$myrow['tagref']."',
                                                                    fechacontable='".$myrow['transdate']."',
                                                                    batchconciliacion= ".$TransNo."
                                                            WHERE banktransid=" . $esteid;
                                                    $ErrMsg =  _('Could not match off this payment because');
                                                    
                                                    //echo $sql;
                                                    $resultCC = DB_query($sql,$db,$ErrMsg);
                                                }
                                            }
                                            
                                        } else {
                                            //prnMsg(_('SIN MATCH !') ,'error');
                                            //prnMsg($line ,'error');
                                           /* SIN MATCH ALGUNO !! */
                                           
                                            $registrosNOMATCH1 = $registrosNOMATCH1 + 1;
                                            
                                        }
                                        
                                    } 
                                    
                                }
                            }
                            
                            $cont = $cont + 1;
                            
                            $k=$k+1;
                              
                        } else {
                            $error = _('Fecha no valida en linea ') . intval($line_num+1);
                            prnMsg($error,'error');
                        }; # Fin de if trae codigo de cliente
                         
                }; # Fin condicion columnas < mincolumnas    
              }; # Fin del for que recorre cada linea
            
            
            prnMsg("SE CARGARON EXITOSAMENTE AL SISTEMA ".$totalRegistrosInsertados." TRANSACCIONES BANCARIAS DE ESTADO DE CUENTA",'sucess');
            

            prnMsg("REGISTROS CONCILIADOS EN AUTOMATICO PRIMER INTENTO ! :".$registrosMATCH1."",'success');
            prnMsg("REGISTROS CONCILIADOS EN AUTOMATICO SEGUNDO INTENTO ! :".$registrosMATCH2."",'success');
            prnMsg("REGISTROS CON MULTIPLES MATCHS AUTOMATICOS ! :".$registrosMULTIMATCH1."",'warn');
            prnMsg("REGISTROS SIN MATCHS AUTOMATICOS ! :".$registrosNOMATCH1."",'error');

        }else{ 
           echo "Ocurrió algún error al subir el fichero. No pudo guardarse."; 
        };
        
    } else {
        $error = _('Tipo de archivo no valido... ');
        prnMsg($error,'error');
    }

}

echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
  echo "<br>";
  echo "<table width=100% cellpadding=3 border = 0>";
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo "<font size=2 color=Darkblue><b>"._('CARGAR DE ESTADO DE CUENTA BANCARIO X MES')."</b></font>";
      echo "</td>";
    echo "</tr>";
    
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo '<table cellpadding=2>';
          echo '<tr>';
            echo '<td style="text-align:center;"><font size=1>fecha</font></td>';
            echo '<td style="text-align:center;"><font size=1>|</font></td>';
            echo '<td style="text-align:center;"><font size=1>concepto</font></td>';
            echo '<td style="text-align:center;"><font size=1>|</font></td>';
            echo '<td style="text-align:center;"><font size=1>retiros</font></td>';
            echo '<td style="text-align:center;"><font size=1>|</font></td>';
            echo '<td style="text-align:center;"><font size=1>depositos</font></td>';
          echo '</tr>';
        echo '</table>';
      echo "</td>";
    echo "</tr>";    
         
echo '<tr><td align=center colspan=2>&nbsp;&nbsp;' . _('Cuenta Bancaria') . ':<select tabindex="1" name="BankAccount">';

$sql = "SELECT accountcode, bankaccountname FROM bankaccounts";
$resultBankActs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultBankActs)){
	if (isset($_POST['BankAccount']) and $myrow["accountcode"]==$_POST['BankAccount']){
		echo "<option selected Value='" . $myrow['accountcode'] . "'>" . $myrow['bankaccountname'];
	} else {
		echo "<option Value='" . $myrow['accountcode'] . "'>" . $myrow['bankaccountname'];
	}
}

echo "</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . _('Mes:');
echo '<select Name="ToMes">';

    $sql = "SELECT * FROM cat_Months";
    $ToMeses = DB_query($sql,$db);
    while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
        $ToMesbase=$myrowToMes['u_mes'];
        if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){ 
            echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '  " selected>' .$myrowToMes['mes'];
        }else{
            echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
        }
    }
echo '</select>';
echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
echo "</td></tr>";    
 
    echo "<tr>";
      echo "<td style='text-align:center;'>";
        echo "<font size=2 >"._('Caracter Separador')." : </font>&nbsp;";
        echo "<input style='text-align:center;' type='text' size=1 maxlength=1 name='separador' value='".$separador."'>&nbsp;&nbsp;";
        echo "&nbsp;&nbsp;<font size=2>". _('Archivo (.csv o .txt)') . " : </font>&nbsp;";
        echo "<input type='file' name='userfile' size=50 >&nbsp;";        
      echo "</td>";
    echo "</tr>";

    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
     
      
        echo "<input type='checkbox' name='eliminarprimero'>"._('BORRAR CONCILIACIONES Y MOVIMIENTOS DEL BANCO DESDE EL <b>DIA</b>:');
        
        echo '<select Name="ToDia">';
        
        $sql = "SELECT * FROM cat_Days";
        $ToMeses = DB_query($sql,$db);
        while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
        	$ToMesbase=$myrowToMes['DiaId'];
        	if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){
        		echo '<option  VALUE="' . $myrowToMes['DiaId'] .  '  " selected>' .$myrowToMes['Dia'];
        	}else{
        		echo '<option  VALUE="' . $myrowToMes['DiaId'] .  '" >' .$myrowToMes['Dia'];
        	}
        }
        echo '</select>';
        
        
        echo _(' <br> ANTES DE SUBIR NUEVOS MOVIMIENTOS...!!!');
      echo "</td>";
    echo "</tr>"; 


    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";

        echo "<input type='submit' name='cargar' value='SUBIR INFORMACION'>";
      echo "</td>";
    echo "</tr>"; 
    
  echo "</table>";
  
echo "</form>";

?>