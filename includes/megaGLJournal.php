<?php

$PageSecurity = 5;

include('includes/DefineJournalClass.php');
include('includes/session.inc');

$title = _('Cargar Mega Poliza...');

include('includes/header.inc');
$funcion=105;
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

if (isset($_POST['commit'])) {
    /* once the GL analysis of the journal is entered
      process all the data in the session cookie into the DB
      A GL entry is created for each GL entry
    */

	$PeriodNo = GetPeriod($_SESSION['JournalDetail']->JnlDate,$db);

     /*Start a transaction to do the whole lot inside */
	$result = DB_Txn_Begin($db);

	$TransNo = GetNextTransNo( 0, $db);

	foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
		$SQL = 'INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag) ';
		$SQL= $SQL . 'VALUES (0,
					' . $TransNo . ",
					'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
					" . $PeriodNo . ",
					" . $JournalItem->GLCode . ",
					'" . $JournalItem->Narrative . "',
					" . $JournalItem->Amount .
					",'".$JournalItem->tag."')";
                                        
                echo $SQL;
                /*
		$ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
		$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                */
                
	}


	$ErrMsg = _('No pude procesar y confirmar los movimientos');
	//$result= DB_Txn_Begin($db);
	$result= DB_Txn_Commit($db);
	
	prnMsg(_('Poliza').' ' . $TransNo . ' '._('ha sido procesada exitosamente'),'success');
	$p=$PeriodNo;
	$datejournal=FormatDateForSQL($_SESSION['JournalDetail']->JnlDate);

	unset($_POST['JournalProcessDate']);
	unset($_POST['JournalType']);
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);

		
	//javascript:Abrir_ventana('popup.html')
	$datejournal = str_replace("/","-",$datejournal);
	$liga="PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=0&TransNo=$TransNo&periodo=$p&trandate=$datejournal";
	echo "<br><a href='$liga' target='_blank'><b>"._('Imprime Poliza').'</b></a>';
	
	/*Set up a newy in case user wishes to enter another */
	//echo "<br><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . "&NewJournal=Yes'>"._('Enter Another General Ledger Journal').'</a>';
	echo "<br><br><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>"._('Introduzca otra Poliza').'</a>';
	/*And post the journal too */
	include ('includes/GLPostings.inc');
	exit;
    
}

if (isset($_POST['cargar']) and $separador<>'')
{

    $nombre_archivo = $_FILES['userfile']['name']; 
    $tipo_archivo = $_FILES['userfile']['type']; 
    $tamano_archivo = $_FILES['userfile']['size']; 

    $filename = 'pricelists/'.$nombre_archivo;
    
    echo "EL TIPO DE ARCHIVO ES:".$tipo_archivo;
     
    if ($tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='application/octet-stream')
    {
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename)){
            
            # UNA VEZ QUE EL ARCHIVO SE CARGO, ENTRA A LEER EL CONTENIDO
            
            #Declara variables
            
            $tieneerrores=0;
            $lineatitulo=0; //-1 para que no verifique columnas del titulo
            $mincolumnas=7;
            
            
            $columnaunidad=0;
            $columnacuenta=2;
            $columnacargos=4;
            $columnaabonos=5;
            $columnaconcepto=6;
            $columnapreciosFin=0;
            
            unset($_SESSION['JournalDetail']->GLEntries);
            unset($_SESSION['JournalDetail']);
        
            $_SESSION['JournalDetail'] = new Journal;
    
            /* Make an array of the defined bank accounts - better to make it now than do it each time a line is added
            Journals cannot be entered against bank accounts GL postings involving bank accounts must be done using
            a receipt or a payment transaction to ensure a bank trans is available for matching off vs statements */
    
            $SQL = 'SELECT accountcode FROM bankaccounts';
            $result = DB_query($SQL,$db);
            $i=0;
            while ($Act = DB_fetch_row($result)){
                    $_SESSION['JournalDetail']->BankAccounts[$i]= $Act[0];
                    $i++;
            }
            
            if (isset($_POST['JournalProcessDate'])){
                    $_SESSION['JournalDetail']->JnlDate=$_POST['JournalProcessDate'];
            
                    if (!Is_Date($_POST['JournalProcessDate'])){
                            prnMsg(_('La fecha capturada no es valida, favor de capturar una fecha en el formato'). $_SESSION['DefaultDateFormat'],'warn');
                            $_POST['CommitBatch']='Do not do it the date is wrong';
                            exit;
                    }
            }
            
            $_SESSION['JournalDetail']->JournalType = 'Normal';
            $msg='';
        
            
            # ABRE ERL ARCHIVO Y LO ALMACENA EN UN OBJETO    
            $lineas = file($filename);
            
            # ****************************
            # **** RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO ****
            # ****************************
            
            echo "<table width=100% cellpadding=3 border=1>";
            echo "<tr>";
              echo "<td style='text-align:center;' colspan=4>";
                echo "<font size=2 color=Darkblue><b>"._('VERIFICACION DE MEGA POLIZA CONTABLE')."</b></font>";
              echo "</td>";
              echo "<td style='text-align:center;'>";
                echo "<font size=2 color=DarkRed><b>".$_POST['JournalProcessDate']."</b></font>";
              echo "</td>";
              echo "<td style='text-align:center;' colspan=2>";
                echo "<font size=2 color=Darkblue><b></b></font>";
              echo "</td>";
              
            echo "</tr>";
           
            $cont=0;
            
            $sumaCargos = 0;
            $sumaAbonos = 0;
            
            foreach ($lineas as $line_num => $line)
              {
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
                    $error .= '<br>'. _('La estructura del archivo debe de tener al menos '.$mincolumnas.' datos separados por "'.$separador.'" y tiene '.$columnaslinea );
                    prnMsg($error,'error');
                    exit;
                }    
                else
                {
                    # ************************************************************  
                    # *** ENTRA A VALIDAR LOS TITULOS DE LAS COLUMNAS SI APLICA ***
                    # ************************************************************

                    if ($line_num == $lineatitulo)
                    {
                        //EN NUESTRO CASO NO APLICA...
                        if (false) {
                            $columnas = count($datos);           # Obtiene el numero de columnas de la linea en base al separador
                            $columnapreciosFin = intval($columnas-1);
    
                            $k=0;
                            
                            # ***************************************
                            # *** Recorre las columnas de los precios
                            # ***************************************
                            
                            for ($j=$columnapreciosIni;$j<=$columnapreciosFin;$j++)
                            {
                                  $listasprecios[$k]=trim($datos[$j]);
                                  
                                  $sql= "select typeabbrev from salestypes where typeabbrev='".$listasprecios[$k]."'";
                                  $result = DB_query($sql,$db);
                                  $myrow = DB_fetch_row($result);
                                  
                                  # ****************************
                                  # **** existe la lista de precio??? ***
                                  # ****************************                            
                                  if (DB_num_rows($result)==0)
                                  {
                                      $error = _('LA LISTA DE PRECIOS "' . $listasprecios[$k] . '" NO ESTA REGISTRADA EN EL SISTEMA. Verificar la linea no. ') . intval($line_num+1);
                                      prnMsg($error,'error');
                                      exit;
                                  }    
                                  else
                                  {
                                      $codigolistaprecios[$k] = $myrow[0]; # Asigna a una pos de array el codigo de la lista de precios
                                  }; # Fin de if existe lista de precios de tabla sales_types
                                  $k=$k+1;
                            }
                        } //SI NO APLICA AQUI TERMINA IF
                    }
                    else
                    {
                        # ********************************
                        # *** RECORRE LAS LINEAS DE DATOS
                        # ********************************
                              
                        # *****************************************************************************
                        # *** COLUMNA UNIDAD DE NEGOCIO ***
                        # *****************************************************************************                          
                          # si viene vacio el codigo del producto
                          if ($datos[$columnaunidad]=='')
                          {
                              $error = _('EL CODIGO DE LA UNIDAD DE NEGOCIOS NO PUEDE IR VACIO .<br> Verificar la linea no. ') . intval($line_num+1);
                              prnMsg($error,'error');
                              exit;
                              
                          } else {
                            
                                $codigovalido = 1;
                                
                                $codigounidad = trim($datos[$columnaunidad]);
                                $sql= "select tagref, tagdescription from tags where tagref='".$codigounidad."'";
                                $result = DB_query($sql,$db);
                                
                                if ($myrow = DB_fetch_array($result)) {
                                    $nombreunidad = $myrow['tagdescription'];
                                } else {
                                    $nombreunidad = '';
                                    $error = _('EL CODIGO DE LA UNIDAD DE NEGOCIOS "' . $codigounidad . '" NO ESTA REGISTRADO EN EL SISTEMA. Verificar la linea no. ') . intval($line_num+1);
                                    prnMsg($error,'error');
                                    $codigovalido = 0;       
                                    //exit;  no salir necesariamente, solo brincarse los productos que no encuentra.
                                }
                                
                          }; # Fin de if trae codigo valido
                          
                        # *****************************************************************************
                        # *** COLUMNA CODIGO DE CUENTA CONTABLE ***
                        # *****************************************************************************                          
                          # si viene vacio el codigo de la cuenta contable
                          if ($datos[$columnacuenta]=='')
                          {
                              
                              $error = _('EL CODIGO DE LA CUENTA CONTABLE NO PUEDE IR VACIO .<br> Verificar la linea no. ') . intval($line_num+1);
                              prnMsg($error,'error');
                              exit;
                              
                          } else {
                            
                                $codigocuenta = trim($datos[$columnacuenta]);
                                $sql= "select accountcode, accountname from chartmaster where accountcode='".$codigocuenta."'";
                                $result = DB_query($sql,$db);
                                
                                if ($myrow = DB_fetch_array($result)) {
                                    $nombrecuenta = $myrow['accountname'];
                                } else {
                                    $nombrecuenta = '';
                                    $error = _('EL CODIGO DE LA CUENTA "' . $codigocuenta . '" NO ESTA REGISTRADO EN EL SISTEMA. Verificar la linea no. ') . intval($line_num+1);
                                    prnMsg($error.' SQL:'.$sql,'error');
                                    $codigovalido = 0;      
                                    //exit;  no salir necesariamente, solo brincarse los productos que no encuentra.
                                }
                                
                          }; # Fin de if trae codigo de producto
                          
                          # *****************************************************************************
                          # *** COLUMNAS DE CARGOS, ABONOS Y CONCEPTO
                          # *****************************************************************************
                          
                            # VALIDA QUE LOS CODIGOS FUERON VALIDOS; SI NO SOLO IGNORAR ESTE ITEM.
                            if ($codigovalido==1) {
                                
                                    # Elimina el registro que ya exista e la tabla prices
                                    /*
                                    if ($_POST['metodo'] == 1) {
                                        $sSQL= "UPDATE locstock";
                                        $sSQL.= " SET reorderlevel = 0";
                                        $result = DB_query($sSQL,$db);
                                    }
                                
                                    if ($_POST['metodo'] == 2) {
                                        
                                    }
                                    */
                                    /*
                                    $sSQL= "DELETE FROM prices";
                                    $sSQL.= " WHERE stockid='".$codigoproducto."'";
                                    $sSQL.= " AND typeabbrev='".$codigolistaprecios[$k]."'";
                                    $sSQL.= " AND currabrev='".$codigomoneda."'";
                                    $sSQL.= " AND debtorno= '".$codigocliente."'";
                                    $sSQL.= " AND areacode= ''";
                                    $result = DB_query($sSQL,$db);
                                    */
                                
                                    # Inserta registro en la tabla prices
                                    
                                    echo "<tr>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=red>".$cont."</font>";
                                    echo "</td>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=Darkblue><b>".$codigounidad.' '.$nombreunidad."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=Darkblue>".$codigocuenta."</font>";
                                    echo "</td>";
                                    echo "<td style='text-align:left;'>";
                                    echo "<font size=2 color=Darkblue>".$nombrecuenta."</font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".number_format($datos[$columnacargos],2)."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".number_format($datos[$columnaabonos],2)."</b></font>";
                                    echo "</td>";
                                    echo "<td style='text-align:right;'>";
                                    echo "<font size=2 color=Darkblue><b>".str_replace('"','',$datos[$columnaconcepto])."</b></font>";
                                    echo "</td>";
                                    echo "</tr>";
                                    
                                    $sumaCargos = $sumaCargos +$datos[$columnacargos];
                                    $sumaAbonos = $sumaAbonos +$datos[$columnaabonos];
                                    
                                    //ALTA DE REGISTRO EN ARREGLO DE SESSION
                                    $_SESSION['JournalDetail']->add_to_glanalysis(($datos[$columnacargos]-$datos[$columnaabonos]),
                                                                                    $datos[$columnaconcepto], $codigocuenta, $codigocuenta, $codigounidad);
                                    
                                    /*
                                    $sSQL= "UPDATE locstock SET reorderlevel = ".$datos[$columnacantidad]." WHERE stockid = '".$codigoproducto."' and loccode = '".$codigoalmacen."'";
                                    $result = DB_query($sSQL,$db);
                                    */
                                    
                                    $cont = $cont + 1;
                                    
                                    $k=$k+1;
                            };
                            
                            
                    };
                }; # Fin condicion columnas < mincolumnas    
              }; # Fin del for que recorre cada linea
            
            
            echo "<tr>";
            echo "<td style='text-align:left;'>";
            echo "<font size=2 color=red></font>";
            echo "</td>";
            echo "<td style='text-align:left;'>";
            echo "<font size=2 color=Darkblue><b>TOTALES</b></font>";
            echo "</td>";
            echo "<td style='text-align:left;'>";
            echo "<font size=2 color=Darkblue></font>";
            echo "</td>";
            echo "<td style='text-align:left;'>";
            echo "<font size=2 color=Darkblue></font>";
            echo "</td>";
            echo "<td style='text-align:right;'>";
            echo "<font size=2 color=Darkblue><b>".number_format($sumaCargos,2)."</b></font>";
            echo "</td>";
            echo "<td style='text-align:right;'>";
            echo "<font size=2 color=Darkblue><b>".number_format($sumaAbonos,2)."</b></font>";
            echo "</td>";
            echo "<td style='text-align:right;'>";
            echo "<font size=2 color=Darkblue><b></b></font>";
            echo "</td>";
            echo "</tr>";
            
            
            echo "</table>";
            
            prnMsg("SE CARGARON EXITOSAMENTE AL SISTEMA ".$cont." LINEA DE MEGA POLIZA.",'sucess');
            
            if ($sumaCargos == $sumaAbonos) {
                
                prnMsg("POLIZA PROCESADA CON EXITO !!! (".($sumaCargos-$sumaAbonos).")",'error');
                
                
                echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
                echo "<br>";
                echo "<table width=100% cellpadding=3>";
                  echo "<tr>";
                    echo "<td style='text-align:center;' colspan=2>";
                      echo "<font size=2 color=Darkblue><b>"._('CONFIRMA PROCESO DE MEGA POLIZA CONTABLE')."</b></font>";
              
                    echo "</td>";
                  echo "</tr>";
                  
            
                  echo "<tr>";
                    echo "<td style='text-align:center;' colspan=2>";
                      echo "<br><br><input type='submit' name='commit' value='PROCESAR Y CONFIRMAR POLIZA...'>";
                    echo "</td>";
                  echo "</tr>"; 
                  
                echo "</table>";
                
                echo "</form>";
                
                exit;
                
            } else {
                prnMsg("LA SUMA DE CARGOS Y ABONOS NO ES IGUAL (".($sumaCargos-$sumaAbonos).")",'error');
                exit;
            }

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
        echo "<font size=2 color=Darkblue><b>"._('CARGA DE MEGA POLIZA CONTABLE')."</b></font>";

      echo "</td>";
    echo "</tr>";
    
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo "<font size=2 color=Darkblue>"._('FORMATO DEL ARCHIVO A SUBIR').":</font>";
        echo "<br><br>";
      echo "</td>";
    echo "</tr>";
    
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo '<table cellpadding=2>';
          echo '<tr>';
            echo '<td style="text-align:center;"><font size=1>CODIGO U.N.</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>NOMBRE U.N.<BR>(SE IGNORA)</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>CUENTA CONTABLE</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>NOMBRE CUENTA<BR>(SE IGNORA)</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>MONTO CARGO</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>MONTO ABONO</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>CONCEPTO</font></td>';
            echo '<td style="text-align:center;"><font size=1></font></td>';
          echo '</tr>';
        echo '</table>';
      echo "</td>";
    echo "</tr>";
    


    echo '<tr>
	    <td width=200 style="text-align:right;"><br><br>'._('Fecha proceso de Poliza').":</td>
            <td style='text-align:left;'>
            <br><br><input type='text' class='date' alt='dd/mm/YYYY'
                    name='JournalProcessDate' maxlength=10 size=11 value='" .
		 $_SESSION['JournalDetail']->JnlDate . "'></td>
            </tr>";	

/*
    echo "<tr><td style='text-align:center;' colspan=2><font size=2><br><br>" . _('Metodo de Actualizacion') . ":&nbsp;
                <select name='metodo'>";

    echo "<option selected value=0>Solo cambia optimos que cambien con esta carga...</OPTION>";
    echo "<option          value=1>Elimina Todos los Optimos Antes de Subir este archivo...</OPTION>";
    echo "</select></td></tr>";
*/

/*
echo "<tr><td style='text-align:center;' colspan=2><font size=2>" . _('Para la Sucursal') . ":&nbsp;
<select name='area'>";

$sql = 'SELECT distinct a.areacode, a.areadescription';
$sql = $sql.' FROM areas a, tags t, sec_unegsxuser uxu';
$sql = $sql.' WHERE a.areacode=t.areacode';
$sql = $sql.' AND t.tagref=uxu.tagref';
$sql = $sql.' AND uxu.userid="'.$_SESSION['UserID'].'"';
$result = DB_query($sql,$db);
if (DB_num_rows($result)>0){
	echo "<option selected value='0'> TODAS </OPTION>";
	
	while ($myrow=DB_fetch_array($result)){
		
		if (isset($_POST['DefaultLocation']) and $myrow['areacode'] == $_POST['DefaultLocation']){
	
			echo "<option selected value='" . $myrow['areacode'] . "'>" . $myrow['areadescription'];
	
		} else {
			echo "<option Value='" . $myrow['areacode'] . "'>" . $myrow['areadescription'];
	
		}
	
	}
}

    echo "</select></td></tr>";    
    
*/    
    echo "<tr>";
      echo "<td colspan=2 style='text-align:center;'><br><br>";
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