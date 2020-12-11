<?php
/**
 * Carga de Mega Póliza Contable
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 07/11/2017
 * Fecha Modificación: 07/11/2017
 * Carga de Mega Póliza Contable
 */

$PageSecurity = 5;
include('includes/DefineJournalClass.php');
include('includes/session.inc');
$funcion=500;
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include "includes/SecurityUrl.php";
//include('includes/MiscFunctions.inc');

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

if (isset($_POST['separador'])) {
    $separador = $_POST['separador'];
} else {
    if (isset($_GET['separador'])) {
        $separador = $_GET['separador'];
    } else {
        $separador = ",";
    }
}

if (isset($_POST['InvoiceYear'])) {
    $InvoiceYear=$_POST['InvoiceYear'];
} else {
    $InvoiceYear=date('Y');
}
if (isset($_POST['InvoiceMonth'])) {
    $InvoiceMonth=$_POST['InvoiceMonth'];
} else {
    $InvoiceMonth=date('m');
}
if (isset($_POST['InvoiceDay'])) {
    $InvoiceDay=$_POST['InvoiceDay'];
} else {
    $InvoiceDay=date('d');
}

$txtFechaPoliza = date('d-m-Y');
if (isset($_POST['txtFechaPoliza'])) {
    $txtFechaPoliza = $_POST['txtFechaPoliza'];
}
//echo "<br>txtFechaPoliza: ".$txtFechaPoliza;

$_POST['JournalProcessDate'] = date_format(date_create($txtFechaPoliza), "d/m/Y"); //$InvoiceDay . '/' . $InvoiceMonth . '/' . $InvoiceYear;
//echo "<br>JournalProcessDate: ".$_POST['JournalProcessDate'];

if (isset($_POST['commit'])) {
    $PeriodNo = GetPeriod($_SESSION['JournalDetail']->JnlDate, $db, $_SESSION['JournalDetail']->JnlTag);
    //$result = DB_Txn_Begin($db);
    $TransNo = 0;
    // print_r($_SESSION['JournalDetail']->GLEntries);
    foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
        if ($TransNo == 0) {
            $TransNo = GetNextTransNo($JournalItem->tipoPoliza, $db);
        }
        $arrfecha = explode("/", $_SESSION['JournalDetail']->JnlDate);
        $fecha =  $arrfecha['2'] . "-" . $arrfecha['1'] . "-" . $arrfecha['0'];
        $SQL = 'INSERT INTO gltrans (type,
        typeno,
        trandate,
        periodno,
        account,
        narrative,
        amount,
        tag,
        userid,
        dateadded,
        ln_ue,
        posted,
        nu_folio_ue
        ) VALUES ('.$JournalItem->tipoPoliza.',
        ' . $TransNo . ",
        '" . $fecha . "',
        " . $PeriodNo . ",
        '" . $JournalItem->GLCode . "',
        '" . $JournalItem->Narrative . "',
        " . $JournalItem->Amount .
        ",'".$JournalItem->tag."',
        '" . $_SESSION['UserID'] . "',
        now(),
        '".$JournalItem->ue."',
        '0',
        '0'
        )";
        //echo "<br>SQL: ".$SQL;
        $ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
        $DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
        $result = DB_query($SQL, $db);
    }

    //$ErrMsg = _('No pude procesar y confirmar los movimientos');
    //$result= DB_Txn_Begin($db);
    //$result= DB_Txn_Commit($db);
    
    //prnMsg(_('Poliza').' ' . $TransNo . ' '._('ha sido procesada exitosamente'), 'success');
    $p=$PeriodNo;
    $datejournal=FormatDateForSQL($_SESSION['JournalDetail']->JnlDate);

    unset($_POST['JournalProcessDate']);
    unset($_POST['JournalType']);
    unset($_SESSION['JournalDetail']->GLEntries);
    unset($_SESSION['JournalDetail']);
        
    //javascript:Abrir_ventana('popup.html')
    $datejournal = str_replace("/", "-", $datejournal);
    $url="&FromCust=>1&ToCust=>1&PrintPDF=>Yes&type=>0&TransNo=>".$TransNo."&periodo=>".$p."&trandate=>".$datejournal;
    $enc = new Encryption;
    $url = $enc->encode($url);
    $liga= "URL=" . $url;
    //echo "<a href='PrintJournal.php?".$liga."' target='_blank'><h3>Póliza ".$TransNo."&nbsp;&nbsp;<img src='images/printer.png' title='Imprimir'></h3></a>";
    //echo "<br><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . "&NewJournal=Yes'>"._('Enter Another General Ledger Journal').'</a>';
    //echo "<br><br><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>"._('Introduzca otra Poliza').'</a>';
    //echo "<a href='".$_SERVER['PHP_SELF']."'><h3> Nueva Póliza</h3></a>";
    //include('includes/GLPostingsV4.inc');
    //include 'includes/footer_Index.inc';
    //exit;
    $mesaj = _('Se generó la poliza con No. Operación: <b>'. $TransNo . '</b> correctamente. ');
    prnMsg($mesaj, 'success');
    
}

if (isset($_POST['cargar']) and $separador<>'') {
    $nombre_archivo = $_FILES['userfile']['name'];
    $tipo_archivo = $_FILES['userfile']['type'];
    $tamano_archivo = $_FILES['userfile']['size'];
    $filename = 'pricelists/'.$nombre_archivo;
     
    if ($tipo_archivo=='text/plain' or $tipo_archivo=='application/vnd.ms-excel' or $tipo_archivo=='text/csv' or $tipo_archivo=='application/octet-stream') {
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename)) {
            $tieneerrores=0;
            $lineatitulo=0; //-1 para que no verifique columnas del titulo
            $mincolumnas=9;
            $columnaunidad=0;
            $columnacuenta=2;
            $columnacargos=4;
            $columnaabonos=5;
            $columnaconcepto=6;
            $columnaUnidadEjecutora=7;
            $columnapreciosFin=0;
            
            unset($_SESSION['JournalDetail']->GLEntries);
            unset($_SESSION['JournalDetail']);
            
            $_SESSION['JournalDetail']= new Journal;
    
            $SQL = 'SELECT accountcode FROM bankaccounts';
            $result = DB_query($SQL, $db);
            $i=0;
            while ($Act = DB_fetch_row($result)) {
                    $_SESSION['JournalDetail']->BankAccounts[$i]= $Act[0];
                    $i++;
            }
            if (isset($_POST['JournalProcessDate'])) {
                $_SESSION['JournalDetail']->JnlDate= $_POST['JournalProcessDate'];
                if (!Is_Date($_POST['JournalProcessDate'])) {
                        prnMsg(_('La fecha capturada no es valida, favor de capturar una fecha en el formato'). $_SESSION['DefaultDateFormat'], 'warn');
                        $_POST['CommitBatch']='Do not do it the date is wrong';
                        exit;
                }
            }
            $_SESSION['JournalDetail']->JournalType = 'Normal';
            $msg='';
        
            # ABRE ERL ARCHIVO Y LO ALMACENA EN UN OBJETO
            $lineas = file($filename);
            // echo "<br>datos file";
            // var_dump($lineas);
            
            # ****************************
            # **** RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO ****
            # ****************************
            
            echo '<table class="table table-bordered">';
            echo '<tr class="header-verde">';
            echo "<td style='text-align:center;' colspan=5>";
            echo "<b>Verificación de Mega Póliza Contable</b>";
            echo "</td>";
            echo "<td style='text-align:center;'>";
            echo "<b>".$_POST['JournalProcessDate']."</b>";
            echo "</td>";
            echo "<td style='text-align:center;' colspan=4>";
            echo "<font size=2 color=Darkblue><b></b></font>";
            echo "</td>";
            echo "</tr>";

            echo "<tr class='header-verde'>";
            echo '<td style="text-align:center;"><font size=1>Línea</font></td>';
            echo '<td style="text-align:center;"><font size=1>Código UR</font></td>';
            echo '<td style="text-align:center;"><font size=1>Nombre UR<BR>(Se Ignora)</font></td>';
            echo '<td style="text-align:center;"><font size=1>Cuenta Contable</font></td>';
            echo '<td style="text-align:center;"><font size=1>Nombre Cuenta<BR>(Se Ignora)</font></td>';
            echo '<td style="text-align:center;"><font size=1>Monto Cargo</font></td>';
            echo '<td style="text-align:center;"><font size=1>Monto Abono</font></td>';
            echo '<td style="text-align:center;"><font size=1>Concepto</font></td>';
            echo '<td style="text-align:center;"><font size=1>UE</font></td>';
            echo '<td style="text-align:center;"><font size=1>Nombre UE<BR>(Se Ignora)</font></td>';
            echo "</tr>";
           
            $cont=0;
            $sumaCargos = 0;
            $sumaAbonos = 0;
            $finDeDatosValidos = 0;
            foreach ($lineas as $line_num => $line) {
                $datos = explode($separador, $line); # Convierte en array cada una de las lineas
                $columnaslinea = count($datos);           # Obtiene el numero de columnas de la linea en base al separador
                
                # ****************************
                # **** PRIMERA VALIDACION ****
                # **** columnas de la linea menores que la minimas requeridas?***
                # ****************************
                if ($columnaslinea<$mincolumnas) {
                    $tieneerrores = 1;
                    $error = _('El número mínimo de columnas requeridas no se cumple en la línea No. ') . intval($line_num+1);
                    $error .= '<br>'. _('Debe tener '.$mincolumnas.' columnas, separadas por "'.$separador.'" y tiene '.$columnaslinea);
                    prnMsg($error, 'error');

                    include 'includes/footer_Index.inc';
                    exit;
                } else {
                    # ************************************************************
                    # *** ENTRA A VALIDAR LOS TITULOS DE LAS COLUMNAS SI APLICA ***
                    # ************************************************************

                    if ($line_num == $lineatitulo) {
                        //EN NUESTRO CASO NO APLICA...
                        if (false) {
                            $columnas = count($datos);           # Obtiene el numero de columnas de la linea en base al separador
                            $columnapreciosFin = intval($columnas-1);
    
                            $k=0;
                            
                            # ***************************************
                            # *** Recorre las columnas de los precios
                            # ***************************************
                            
                            for ($j=$columnapreciosIni; $j<=$columnapreciosFin; $j++) {
                                  $listasprecios[$k]=trim($datos[$j]);
                                  
                                  $sql= "select typeabbrev from salestypes where typeabbrev='".$listasprecios[$k]."'";
                                  $result = DB_query($sql, $db);
                                  $myrow = DB_fetch_row($result);
                                  
                                  # ****************************
                                  # **** existe la lista de precio??? ***
                                  # ****************************
                                if (DB_num_rows($result)==0) {
                                    $error = _('LA LISTA DE PRECIOS "' . $listasprecios[$k] . '" NO ESTA REGISTRADA EN EL SISTEMA. Verificar la linea No. ') . intval($line_num+1);
                                    prnMsg($error, 'error');
                                    exit;
                                } else {
                                    $codigolistaprecios[$k] = $myrow[0]; # Asigna a una pos de array el codigo de la lista de precios
                                }; # Fin de if existe lista de precios de tabla sales_types
                                  $k=$k+1;
                            }
                        } //SI NO APLICA AQUI TERMINA IF
                    } else {
                        if ($finDeDatosValidos == 0) {
                            # ********************************
                            # *** RECORRE LAS LINEAS DE DATOS
                            # ********************************
                                  
                            # *****************************************************************************
                            # *** COLUMNA UNIDAD DE NEGOCIO ***
                            # *****************************************************************************
                            # si viene vacio el codigo del producto
                            if ($datos[$columnaunidad]=='') {
                                $error = _('El Código de la Unidad Responsable no puede ir vacío.<br> Verificar la linea No. ') . intval($line_num+1);
                                prnMsg($error, 'error');
                                exit;
                            } elseif (stripos($datos[$columnaunidad], 'TOTALES') > 0) {
                                $error = _('LLEGO AL FIN DE ARCHIVO, LINEA DE TOTALES ES LA LINEA:') . intval($line_num+1);
                                prnMsg($error, 'success');
                                $finDeDatosValidos = 1;
                            } else {
                                  $codigovalido = 1;
                                    
                                  $codigounidad = trim($datos[$columnaunidad]);
                                  $sql= "SELECT tagref, tagdescription FROM tags WHERE tagref='".$codigounidad."'";
                                  $result = DB_query($sql, $db);
                                    
                                if ($myrow = DB_fetch_array($result)) {
                                    $nombreunidad = $myrow['tagdescription'];
                                } else {
                                    $nombreunidad = '';
                                    $error = _('El Código de la Unidad Responsable "' . $codigounidad . '" no se encuentra en el sistema.<br> Verificar la linea No. ') . intval($line_num+1);
                                    prnMsg($error, 'error');
                                    $codigovalido = 0;
                                }
                            }; # Fin de if trae codigo valido

                            # *****************************************************************************
                            # *** COLUMNA UNIDAD EJECUTORA ***
                            # *****************************************************************************
                            # si viene vacio el codigo del producto
                            if ($datos[$columnaUnidadEjecutora]=='') {
                                $error = _('El Código de la Unidad Ejecutora no puede ir vacío.<br> Verificar la linea No. ') . intval($line_num+1);
                                prnMsg($error, 'error');
                                exit;
                            } else {
                                  $codigovalido = 1;
                                    
                                  $codigounidadEjecutora = trim($datos[$columnaUnidadEjecutora]);
                                  $sql= "SELECT ue, desc_ue FROM tb_cat_unidades_ejecutoras WHERE ue='".$codigounidadEjecutora."'";
                                  $result = DB_query($sql, $db);
                                    
                                if ($myrow = DB_fetch_array($result)) {
                                    $nombreunidadEjecutora = $myrow['desc_ue'];
                                } else {
                                    $nombreunidadEjecutora = '';
                                    $error = _('El Código de la Unidad Ejecutora "' . $codigounidadEjecutora . '" no se encuentra en el sistema.<br> Verificar la linea No. ') . intval($line_num+1);
                                    prnMsg($error, 'error');
                                    $codigovalido = 0;
                                }
                            }; # Fin de if trae codigo valido
                              
                            # *****************************************************************************
                            # *** COLUMNA CODIGO DE CUENTA CONTABLE ***
                            # *****************************************************************************
                            # si viene vacio el codigo de la cuenta contable
                            if ($datos[$columnacuenta]=='' and $finDeDatosValidos == 0) {
                                $error = _('El Código de la Cuenta Contable no puede ir vacío.<br> Verificar la linea No. ') . intval($line_num+1);
                                prnMsg($error, 'error');
                                exit;
                            } else {
                                if ($finDeDatosValidos == 0) {
                                    $codigocuenta = trim($datos[$columnacuenta]);
                                    $sql= "SELECT accountcode, accountname from chartmaster where accountcode='".$codigocuenta."'";
                                    $result = DB_query($sql, $db);
                                        
                                    if ($myrow = DB_fetch_array($result)) {
                                        $nombrecuenta = $myrow['accountname'];
                                    } else {
                                        $nombrecuenta = '';
                                        $error = _('El Código de la Cuenta Contable '.$codigocuenta.' no se encuentra en el sistema.<br> Verificar la linea No. ') . intval($line_num+1);
                                        prnMsg($error, 'error');
                                        $codigovalido = 0;
                                        //exit;  no salir necesariamente, solo brincarse los productos que no encuentra.
                                    }
                                }
                            }; # Fin de if trae codigo de producto
                              
                            # *****************************************************************************
                            # *** COLUMNAS DE CARGOS, ABONOS Y CONCEPTO
                            # *****************************************************************************

                            # VALIDA QUE LOS CODIGOS FUERON VALIDOS; SI NO SOLO IGNORAR ESTE ITEM.
                            if ($codigovalido==1 and $finDeDatosValidos == 0) {
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
                                    echo "<td style='text-align:left;'>".($cont+1)."</td>";
                                    echo "<td style='text-align:left;'><b>".$codigounidad."</b></td>";
                                    echo "<td style='text-align:left;'><b>".$nombreunidad."</b></td>";
                                    echo "<td style='text-align:left;'>".$codigocuenta."</td>";
                                    echo "<td style='text-align:left;'>".$nombrecuenta."</td>";
                                    echo "<td style='text-align:right;'><b>".number_format($datos[$columnacargos], 2)."</b></td>";
                                    echo "<td style='text-align:right;'><b>".number_format($datos[$columnaabonos], 2)."</b></td>";
                                    echo "<td style='text-align:left;'><b>".str_replace('"', '', $datos[$columnaconcepto])."</b></td>";
                                    echo "<td style='text-align:left;'><b>".$codigounidadEjecutora."</b></td>";
                                    echo "<td style='text-align:left;'><b>".$nombreunidadEjecutora."</b></td>";
                                    echo "</tr>";

                                    $sumaCargos = $sumaCargos +$datos[$columnacargos];
                                    $sumaAbonos = $sumaAbonos +$datos[$columnaabonos];
                                        
                                    //ALTA DE REGISTRO EN ARREGLO DE SESSION
                                    // $res=$_SESSION['JournalDetail']->Add_To_GLAnalysis(
                                    //     ($datos[$columnacargos]-$datos[$columnaabonos]),
                                    //     $datos[$columnaconcepto],
                                    //     $codigocuenta,
                                    //     $codigocuenta,
                                    //     $codigounidad
                                    // );

                                    $res = $_SESSION ['JournalDetail']->Add_To_GLAnalysis(
                                        ($datos[$columnacargos]-$datos[$columnaabonos]),
                                        $datos[$columnaconcepto],
                                        $codigocuenta,
                                        $nombrecuenta,
                                        $codigounidad,
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        $codigounidadEjecutora,
                                        $nombreunidadEjecutora,
                                        $_POST['selectTipoolizaMega']
                                    );
                                        
                                    /*
                                    $sSQL= "UPDATE locstock SET reorderlevel = ".$datos[$columnacantidad]." WHERE stockid = '".$codigoproducto."' and loccode = '".$codigoalmacen."'";
                                    $result = DB_query($sSQL,$db);
                                    */
                                        
                                    $cont = $cont + 1;
                                        
                                    $k=$k+1;
                            };
                        };
                    };
                }; # Fin condicion columnas < mincolumnas
            }; # Fin del for que recorre cada linea
            
            echo "<tr>";
            //echo "<td style='text-align:left;'></td>";
            echo "<td style='text-align:left;'  colspan='5' ><b>TOTALES</b></td>";
            //echo "<td style='text-align:left;'></td>";
            //echo "<td style='text-align:left;'></td>";
            echo "<td style='text-align:right;'><b>".number_format($sumaCargos, 2)."</b></td>";
            echo "<td style='text-align:right;'><b>".number_format($sumaAbonos, 2)."</b></td>";
            echo "</tr>";
            echo "</table>";
            //prnMsg("SE CARGARON EXITOSAMENTE AL SISTEMA ".$cont." LINEA DE MEGA POLIZA.", 'sucess');
            if (abs($sumaCargos - $sumaAbonos) < 1) {
                //prnMsg("POLIZA CORRECTA !!! (".($sumaCargos-$sumaAbonos).")", 'info');
                
                echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
                echo "<br>";
                echo '<div align="center">';
                //echo "<input type='submit' name='commit' value='PROCESAR Y CONFIRMAR POLIZA...'>";
                echo '<component-button type="submit" id="commit" name="commit" value="Procesar Póliza"></component-button>';
                echo '</div>';
                echo "</form>";
                include 'includes/footer_Index.inc';
                exit;
            } else {
                prnMsg("La suma de los Cargos y Abonos es diferente (".($sumaCargos-$sumaAbonos).")", 'error');
                include 'includes/footer_Index.inc';
                exit;
            }
        } else {
            echo "No fue posible cargar el archivo";
        };
    };
}




echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
  echo "<br>";
  echo '<table class="table table-bordered table-condensed fts12">';
    // echo "<tr>";
    // echo "<td style='text-align:center;' colspan=2>";
    // echo "<font size=2 color=Darkblue><b>"._('CARGA DE MEGA POLIZA CONTABLE')."</b></font>";
    // echo "</td>";
    // echo "</tr>";
    // echo "<tr>";
    // echo "<td style='text-align:center;' colspan=2>";
    // echo "<font size=2 color=Darkblue>"._('FORMATO DEL ARCHIVO A SUBIR').":</font>";
    // echo "<br><br>";
    // echo "</td>";
    // echo "</tr>";
    echo "<tr class='header-verde'>";
    echo '<td style="text-align:center;"><font size=1>Código UR</font></td>';
    //echo '<td style="text-align:center;"><font size=1>,</font></td>';
    echo '<td style="text-align:center;"><font size=1>Nombre UR<BR>(Se Ignora)</font></td>';
    //echo '<td style="text-align:center;"><font size=1>,</font></td>';
    echo '<td style="text-align:center;"><font size=1>Cuenta Contable</font></td>';
    //echo '<td style="text-align:center;"><font size=1>,</font></td>';
    echo '<td style="text-align:center;"><font size=1>Nombre Cuenta<BR>(Se Ignora)</font></td>';
    //echo '<td style="text-align:center;"><font size=1>,</font></td>';
    echo '<td style="text-align:center;"><font size=1>Monto Cargo</font></td>';
    //echo '<td style="text-align:center;"><font size=1>,</font></td>';
    echo '<td style="text-align:center;"><font size=1>Monto Abono</font></td>';
    //echo '<td style="text-align:center;"><font size=1>,</font></td>';
    echo '<td style="text-align:center;"><font size=1>Concepto</font></td>';
    //echo '<td style="text-align:center;"><font size=1></font></td>';
    echo '<td style="text-align:center;"><font size=1>UE</font></td>';
    echo '<td style="text-align:center;"><font size=1>Nombre UE<BR>(Se Ignora)</font></td>';
    echo "</tr>";
    echo '</table>';

    echo '<div class="row"></div>';
    echo '<div class="col-md-4">';
    echo '<component-date-label label="Fecha Póliza: " id="txtFechaPoliza" name="txtFechaPoliza" value="'.$txtFechaPoliza.'"></component-date-label>';
    echo '</div>';
    echo '<div class="col-md-4">';
    echo "<input type='file' name='userfile' size=50 />";
    echo '</div>';
    echo '<div class="col-md-4">';
    echo '<component-text-label label="Separador:" id="separador" name="separador" value="'.$separador.'" maxlength="1"></component-text-label>';
    echo '</div>';
    echo '<div class="row"></div>';

    $SQL="SELECT typeid, typename FROM systypescat WHERE nu_mega_poliza = 1;";
    $result = DB_query($SQL,$db);
    $optionTipoPoliza = "";

    while ($myTipoPoliza = DB_fetch_array($result)) {
        if(isset($_POST['selectTipoolizaMega']) and $_POST['selectTipoolizaMega'] == $myTipoPoliza['typeid']){
            $optionTipoPoliza .= "<option value='".$myTipoPoliza['typeid']."' selected> ".$myTipoPoliza['typename']."</option>";
        }else{
            $optionTipoPoliza .= "<option value='".$myTipoPoliza['typeid']."'> ".$myTipoPoliza['typename']."</option>";
        }
    }

    echo   '<br><div class="col-md-4"> 
                <div class="form-inline row <?= $ocultaDepencencia ?>">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Tipo Operación: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="selectTipoolizaMega" name="selectTipoolizaMega" class="form-control selectTipoolizaMega">
                        '.$optionTipoPoliza.'
                        </select>
                    </div>
                </div>
            </div>';
    echo '<div class="row"></div>';


$cssH = "25%";
if(isset($_POST['cargar'])){
    $cssH = "1%";
}

echo '<br><div align="center" style = "margin-bottom: '.$cssH.'">';
echo '<component-button type="submit" id="cargar" name="cargar" value="Subir Información"></component-button>';
echo '</div>';
  
echo "</form>";

include 'includes/footer_Index.inc';
