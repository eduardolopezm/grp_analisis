<?php
/*ini_set('display_errors', 1); 
ini_set('log_errors', 1); */
// Cabios anticipos
// Cambios anticipos
// se manda a produccion
////
function RegistrarNotasAnticipo($tipoCambioFac = 0,$montoAnticipo=0,$tagref, $monedaFactura, $fechaemision, $DebtorNo, $DebtorTransIDFactura,  $tieneIVA,$idAnticipo,$ivaFactura, $db, $puntoVenta = 0, $nocuentaPuntoVenta = '', $nomClientePuntoVenta = ''){
    // *********************************************** //
    // ********************************************** //
    // ******** No dejar echo en la funcion *********//
    // ******** Declarar Variables a usar ********  //
    // ******** Afecta al punto de venta ********* //
    // ****************************************** //
    // ***************************************** //
    
    //global $db;
    $systype_doc = 13;
    /*$tipocambio = $_SESSION['Items' . $identifier]->CurrencyRate;
    $montoAnticipo = $LineAnticipo->Monto;
    $moneda  = $_SESSION['Items' . $identifier]->CurrAbrev;
    $fecha = split("-", $fechaemision) ;
    $DebtorNo = $_SESSION['Items' . $identifier]->DebtorNo;
    $tagref = $_SESSION['Items' . $identifier]->Tagref;
    $tieneIVA =  $tieneIVA;
    $idAnticipo = $LineAnticipo->TransID;*/

    //echo "<br> AnticipoIVA: ".$tieneIVA;
    $tipocambio = $tipoCambioFac;
    $montoAnticipo = $montoAnticipo;
    $monto = $montoAnticipo;
    $moneda  = $monedaFactura;
    $fecha = explode("-", $fechaemision) ;
    $DebtorNo = $DebtorNo;
    $tagref = $tagref;
    $tieneIVA =  $tieneIVA;
    $idAnticipo = $idAnticipo;
    $ErrMsg = '';
    $DbgMsg = '';

    $InputError = 0;

    $FromDia = $fecha [2];
    $FromMes = $fecha [1];
    $FromYear  = $fecha [0];
    $_POST['aplparcial'] = 2; // PARA TIMBRAR
    $_POST['cmbUsoCfdi'] = 'G02'; 
    $_POST['cmbTipoComprobante'] = 'E';
    $_POST['cmbMetodoPago'] = 'PUE';
    $_POST['cmbTipoRelacion'] = '07';
    $_POST['paymentname'] = 'Aplicacion de anticipos';
    $vrcodepay = utf8_decode('Aplicacion de anticipos');
    $concepto = utf8_decode('Aplicacion de anticipos');    
    $vrcodeasat = '30';
    //$idSaldoAnticipo = $LineAnticipo->TransID;
    $DebtorTransID = $DebtorTransIDFactura ;

    $SQLDatosFactura= "SELECT debtortrans.*, name FROM debtortrans
                INNER JOIN debtorsmaster ON debtortrans.debtorno = debtorsmaster.debtorno
                WHERE id =" .$DebtorTransIDFactura;
    $resultFactura = DB_query($SQLDatosFactura, $db);
    $myrowFactura = DB_fetch_array($resultFactura);
    $TotalFactura = $myrowFactura['ovamount'] + $myrowFactura['ovgst'];
    $typeFactura = $myrowFactura['type'];
    $nombrecliente= $myrowFactura['name'];
    

    //echo "<br> Tipo cambio BIEN".$tipoCambioFacBIEN;

    $_POST ['TaxCat'] = 2;
    if($tieneIVA == '1'){
        $_POST ['TaxCat'] = 4;
    }

    $nocuenta = '';
    if ($puntoVenta == 1) {
        $nocuenta = $nocuentaPuntoVenta;
    } else {
        if (isset($_POST ['nocuenta'])) {
            $nocuenta = $_POST ['nocuenta'];
        }
    }

    $nomCliente = '';
    if ($puntoVenta == 1) {
        $nomCliente = $nomClientePuntoVenta;
    } else {
        if (isset($nombrecliente)) {
            $nomCliente = $nombrecliente;
        }
    }

    $_POST ['nocuenta'] = $nocuenta;

    $nombrecliente = $nomCliente;

    $SQLSaldoANT = "SELECT debtortrans.*, bien.rate as cambiobien FROM debtortrans 
            INNER JOIN debtortrans bien ON bien.id = debtortrans.ref1
     WHERE debtortrans.id ='".$idAnticipo."'"; 

    //echo "<br> saldo".$SQLSaldoANT;
    $resultSaldoANT = DB_query($SQLSaldoANT, $db);
    $rowSaldoANT = DB_fetch_array($resultSaldoANT);
    $monedaSaldoANT = $rowSaldoANT['currcode'];
    $rateSaldoANT = $rowSaldoANT['rate'];
    $ivaSaldoANT = $rowSaldoANT['ovgst'];
    $tipoCambioFacBIEN= $rowSaldoANT['cambiobien'];
    

    //echo "<br> Pruebas: ".$rowSaldoANT;
    //echo "<br> saldo ANt: ".$rateSaldoANT;
    if ($InputError != 1) {
            $Result = DB_Txn_Begin($db);
            
            // Obtiene el trans no que le corsesponde en base al tagref y al $systype_doc
            $transno = GetNextTransNo($systype_doc, $db);
            
            $taxrate = 0;
            $montoiva = 0;
            $rate = ( $tipocambio);
            $DefaultDispatchDate = $FromDia . '/' . $FromMes . '/' . $FromYear;
            $fechaini = rtrim($FromYear) . '-' . rtrim($FromMes) . '-' . rtrim($FromDia);
            $diae = rtrim($FromDia);
            $mese = rtrim($FromMes);
            $anioe = rtrim($FromYear);
            $horax = date('H:i:s');
            $horax = strtotime($horax);
            $hora = date('H');
            $minuto = date('i');
            $segundo = date('s');
            $fechainic = mktime($hora, $minuto, $segundo, rtrim($mese), rtrim($diae), rtrim($anioe));
            $fechaemision = date("Y-m-d H:i:s", $fechainic);
            $PeriodNo = GetPeriod($DefaultDispatchDate, $db, $tagref);
            $DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);
            
            if (isset($_POST ['TaxCat']) and $_POST ['TaxCat'] != "") {
                $sqliva = "SELECT taxrate,taxglcode,taxglcodepaid
                 FROM taxauthrates, taxauthorities
                WHERE taxauthrates.taxauthority=taxauthorities.taxid
                AND taxauthrates.taxcatid =" . $_POST ['TaxCat'];

                //echo "<br> SQL IVA".$sqliva;

                $result = DB_query($sqliva, $db);
                $myrow = DB_fetch_row($result);
                $taxrate = $myrow [0];
                $taxglcode = $myrow [1];
                $taxglcodepaid = $myrow [2];
            }

            // calcula iva y desglosa de iva
            // $montosiniva = $monto / (1 + $taxrate);
            // $montoiva = $monto - $montosiniva;

            $mntIVA = 0;
            if (isset($_POST['mntIVA'])) {
                $mntIVA = $_POST['mntIVA'];
            }

            // calcula iva y desglosa de iva
            if (($mntIVA == 0 || empty($mntIVA))) {
                //Iva del categoria impuestos
                $montosiniva = $monto / (1 + $taxrate);
                $montoiva = $monto - $montosiniva;
            }else{
                //Iva agregado manual
                $_SESSION['IvaManualAbonoDirectoClientes'] = 1;
                //
                $montosiniva = $monto - $mntIVA;
                //$montosiniva = $monto;
                $montoiva = $mntIVA;
            } 
            // se valida ya que debe de acuerdo al iva de l factura
            //echo "<br> ivaFactura2: ".$ivaFactura;
           if( (Round($TotalFactura,2) -  Round($monto,2) < -.01) AND $tieneIVA ==1 AND $ivaFactura == 0){
               $monto = $montosiniva;
               $montoiva  = $montosiniva - ($montosiniva/1.16);
               $montosiniva  = ($montosiniva/1.16);
               $montoAnticipo = $monto;
            }
            
            // Datos del Periodo y fecha ***
            // $DefaultDispatchDate=Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate());
            // $PeriodNo = GetPeriod($DefaultDispatchDate, $db);
            
            // $DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);
            // *****************************
            if ($_SESSION['MostrarCuentaNC']=='1') {
                $SQL="SELECT accountcode,
                concept AS accountname
                FROM chartbridge
                WHERE accountcode='".$_POST['GLCode']."'
                ORDER BY accountcode";
                $ResultR=DB_query($SQL, $db);
                $rowR=DB_fetch_array($ResultR);
                $concepto.=' '.$rowR['accountname'];
            }
            // Realiza el insert en la tabla de debtortrans
            $SQL = "INSERT INTO debtortrans (`tagref`,
                        `transno`,`type`,
                        `debtorno`,`branchcode`,
                        `origtrandate`,`trandate`,`prd`,
                        `settled`,`reference`,`tpe`,`order_`,
                        `rate`,`ovamount`,`ovgst`,`ovfreight`,
                        `ovdiscount`,`diffonexch`,`alloc`,`invtext`,
                        `shipvia`,`edisent`,`consignment`,`folio`,`ref1`,`ref2`,`currcode`,paymentname,nocuenta,userid)";
            $SQL .= " VALUES (" . $tagref . ",
            " . $transno . "," . $systype_doc . ",
            '" . $DebtorNo . "', '" . $DebtorNo . "','" . $fechaemision . "','" . $fechaini . "'," . $PeriodNo . ",'0','" . $concepto . "', '','0','" . $rate . "'," . ($montosiniva * - 1) . "," . ($montoiva * - 1) . ",'0','0','0','0','','1','0','', 'FANT','NCR','NCR','" . $moneda . "','" . $_POST ['paymentname'] . "','" . $_POST ['nocuenta'] . "','".$_SESSION['UserID']."')";
           // echo '<pre>sql NC:'.$SQL;
            $result = DB_query($SQL, $db);
            
            $DebtorTransIDND = DB_Last_Insert_ID($db, 'debtortrans', 'id');

            // ***************************************************************************************************
            // **** CFDI Relacionados ****
            // ***************************************************************************************************
            // 
            //******************GENERAR NOTA DE CARGO  Y SE HACE LA APLICACION YA QUE ES SIN CONTABILIDAD******************        
            $systype_docN = 21;
            $transnoN = GetNextTransNo($systype_docN, $db);        
            $SQLN = "INSERT INTO debtortrans (`tagref`,
                        `transno`,`type`,
                        `debtorno`,`branchcode`,
                        `origtrandate`,`trandate`,`prd`,
                        `settled`,`reference`,`tpe`,`order_`,
                        `rate`,`ovamount`,`ovgst`,`ovfreight`,
                        `ovdiscount`,`diffonexch`,`alloc`,`invtext`,
                        `shipvia`,`edisent`,`consignment`,`folio`,`ref1`,`ref2`,`currcode`,paymentname,nocuenta,userid)";
            $SQLN .= " VALUES (" . $tagref . ",
            " . $transnoN . "," . $systype_docN . ",
            '" . $DebtorNo . "', '" . $DebtorNo . "','" . $fechaemision . "','" . $fechaini . "'," . $PeriodNo . ",'0','" . $concepto . "', '','0','" . $rateSaldoANT . "'," . ($montosiniva) . "," . ($montoiva) . ",'0','0','0','0','','1','0','', 'FANT','NCR','NCR','" . $moneda . "','" . $_POST['paymentname'] . "','" . $_POST ['nocuenta'] . "','".$_SESSION['UserID']."')";
            //echo '<pre>sql NC:'.$SQLN;
            $result = DB_query($SQLN, $db);
            $DebtorTransIDCargo = DB_Last_Insert_ID($db, 'debtortrans', 'id');

            $ISQL = "INSERT INTO custallocns (datealloc,transid_allocfrom,amt,transid_allocto, rate_to,currcode_to, rate_from, currcode_from)
                         VALUES ( NOW()," . $idAnticipo . ',' . abs($montoAnticipo) . ',' . $DebtorTransIDCargo . ',"'.$tipocambio.'", "'.$moneda.'", "'.$rate.'", "'.$moneda.'" )';
            $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);

            $ISQL = "UPDATE debtortrans SET alloc = alloc +  ".($montoAnticipo * -1 )." WHERE id = '".$idAnticipo."' " ;
            $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);


            $ISQL = "UPDATE debtortrans SET alloc = alloc +  ".abs($montoAnticipo).", settled=1 WHERE id = '".$DebtorTransIDCargo."' " ;
            $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);

            //********************GENERAR NOTA DE CARGO  Y SE HACE LA APLICACION YA QUE ES SIN CONTABILIDAD********************
            $percentDescription="";
            if ($_SESSION['FacturaVersion']=="3.3") {

                $queryInsert="INSERT INTO notesorders_invoice(transid,transid_relacion,monto, tiporelacion_relacion, trandate)";
                                $queryInsert.=" VALUES ('".$DebtorTransIDND."','".$DebtorTransID."',".str_replace(",", "", $monto  ).", '".$_POST['cmbTipoRelacion']."', NOW())";


               // echo " Consulta: <br> notesordes: ".$queryInsert;
                $result = DB_query($queryInsert, $db);

                // SE HACE LA RELACION DE FACTURA CON NOTA DE CREDITO
                $ISQL = "INSERT INTO custallocns (datealloc,transid_allocfrom,amt,transid_allocto, rate_to,currcode_to, rate_from, currcode_from )
                                        VALUES (NOW()," . $DebtorTransIDND . ',' . abs($monto  ) . ',' . $DebtorTransID . ', "'.$tipocambio.'", "'.$moneda.'", "'.$rate.'", "'.$moneda.'")';
                //echo "<br> PRUEBAS: ".$ISQL;
                $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);

                $ISQL = "UPDATE debtortrans SET alloc = alloc +  ". (abs($monto ) * -1 ).", settled=1 WHERE id = '".$DebtorTransIDND."' " ;
                //echo "<br> PRUEBAS: ".$ISQL;
                $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
                $ISQL = "UPDATE debtortrans SET alloc = alloc +  ".abs($monto)." WHERE id = '".$DebtorTransID."' " ;
                //echo "<br> PRUEBAS: ".$ISQL;
                $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
                // SE HACE LA RELACION DE FACTURA CON NOTA DE CREDITO
                
                
                if ($result && 1 == 2) {
                    $banderaEx = 0;
                    if ($banderaEx > 0) {
                        while ($rse = DB_fetch_array($rrr)) {
                            $queryFolio="SELECT REPLACE(folio,'|','') as folio FROM debtortrans WHERE id = '".$rse['transid_relacion']."'";
                                $resultFolio = DB_query($queryFolio, $db);
                                $myrowFolio = DB_fetch_array($resultFolio);
                            $percentDescription .= ", aplicado al folio: " .$myrowFolio['folio'] ;// ", " . number_format((($rse['monto']/$monto) * 100), '0', '.', '') ." % al saldo del folio: " .$myrowFolio['folio'] ;
                        }
                    } else {
                        $queryFolio="SELECT REPLACE(folio,'|','') as folio FROM debtortrans WHERE id = '".$_POST['InvoiceTransId_'.$index]."'";
                        $resultFolio = DB_query($queryFolio, $db);
                        $myrowFolio = DB_fetch_array($resultFolio);
                        $percentDescription .= ", aplicado al folio: " .$myrowFolio['folio'] ;//$percentDescription .= ", " . number_format((( str_replace(",", "", $_POST['InvoiceAmount_'.$index])/$monto) * 100), '0', '.', '') ." % al saldo del folio: " .$myrowFolio['folio'] ;
                    }
                }
                //SE CREA INSERT PARA REGISTRAR LA RELACION DE LA FACTURA CON EL ANTICIPO
                $SQLAnt = "UPDATE salesinvoiceadvance  SET  transidncredito = '".$DebtorTransIDND."' , montoncredito = '".str_replace(",", "", $monto)."', transidncargo = '".$DebtorTransIDCargo."' WHERE transidinvoice = '".$DebtorTransID."' AND transidanticipo = '".$idAnticipo."';";
                $resultado = DB_query($SQLAnt, $db);

                //SE CREA INSERT PARA REGISTRAR LA RELACION DE LA FACTURA CON EL ANTICIPO

                $querySelect="select notesorders_invoice.transid_relacion,(abs(notesorders_invoice.monto)) AS ovamount  ,codesat,c_paymentid,paymentname
                                    from notesorders_invoice
                                    left join debtortrans on notesorders_invoice.transid_relacion = debtortrans.id
                                    where notesorders_invoice.transid='".$DebtorTransIDND."'
                                    order by notesorders_invoice.monto desc limit 1;";
                $resultSelect = DB_query($querySelect, $db);

                if (DB_num_rows($resultSelect)) {
                    $myrowResult=DB_fetch_array($resultSelect);

                    if ($myrowResult['codesat']==null or $myrowResult['codesat']=='') {
                        $queryGetCod="SELECT debtortrans.paymentname, paymentmethods.codesat
                                    FROM debtortrans 
                                    INNER JOIN paymentmethods ON  paymentmethods.paymentname= debtortrans.paymentname
                                    WHERE id='".$myrowResult['transid_relacion']."'
                                    LIMIT 1";
                        $resultGetc = DB_query($queryGetCod, $db);
                        if (DB_num_rows($resultGetc)) {
                            $myrowGet=DB_fetch_array($resultGetc);

                            //$vrcodeasat=$myrowGet['codesat'];
                            //$vrcodepay=$myrowGet['paymentname'];
                        }
                        if ($_SESSION['UserID'] == 'desarrollo') {
                            //echo '<pre> paymentname:'.$queryGetCod;
                            //echo htmlentities($arrayGeneracion['xml']);
                        }
                    } else {
                        //$vrcodeasat=$myrowResult['codesat'];
                        //$vrcodepay=$myrowResult['paymentname'];
                    }

                    $vrcodepayid= $_POST['cmbMetodoPago'];
                
                    
                    $percentDescription = ""; // se deja vacio ya que la descripción solo permite 1000 caracteres, si no se timbra, se agrega solo en PDF

                    $queryUpdate="UPDATE debtortrans SET paymentname='".$vrcodepay."',codesat='".$vrcodeasat."',c_TipoDeComprobante='E',c_paymentid='".$vrcodepayid."',c_UsoCFDI='".$_POST['cmbUsoCfdi'] ."',invtext=concat(invtext,' ', '".$percentDescription."') WHERE id='".$DebtorTransIDND."'";
                    //echo "<br>sqlUpdate:<pre>".$queryUpdate;

                    $result = DB_query($queryUpdate, $db);
                } else {
                    $percentDescription = ""; // se deja vacio ya que la descripción solo permite 1000 caracteres, si no se timbra, se agrega solo en PDF

                    $queryUpdate="UPDATE debtortrans SET paymentname='".$_POST['paymentname']."',codesat='".$vrcodeasat."',c_TipoDeComprobante='E',c_paymentid='".$_POST['cmbMetodoPago']."',c_UsoCFDI='".$_POST['cmbUsoCfdi'] ."',invtext=concat(invtext,' ', '".$percentDescription."') WHERE id='".$DebtorTransIDND."'";
                    //echo "<br>sqlUpdate:<pre>".$queryUpdate;

                    $result = DB_query($queryUpdate, $db);
                }
            }
            
            
            // ***************************************************************************************************
            // **** AFECTACIONES CONTABLES ****
            // ***************************************************************************************************
            $rmontosiniva = ($montosiniva / $rate);
            $rmontosinivaANT = ($montosiniva / $tipoCambioFacBIEN);
            $rmontoiva = ($montoiva / $rate);
            $rmontoivaP = ($montoiva / $rateSaldoANT);
            $rmonto = ($monto / $rate);

            /*echo "<br> MONTO; TOTAL total: ".$monto." ".$rate." ".($monto/$rate);
            echo "<br> MONTO; TOTAL montoiva : ".$montoiva." ".$rateSaldoANT." ".($montoiva/$rateSaldoANT);
            echo "<br> MONTO; TOTAL MONTO SIN IVA : ".$montosiniva." ".$rate." ".($montosiniva/$rate);
            echo "<br> MONTO; TOTAL MONTO SIN IVA tipoCambioFacBIEN : ".$montosiniva." ".$tipoCambioFacBIEN." ".($montosiniva/$tipoCambioFacBIEN);*/
            //$montoiva  = $ivaNotaCredito/$rate;
            //$rmonto = $montoNotaCredito/$rate;
            
            // Obtiene la cuentas contables que se afectaran
            // *****************************************
            // Se afecta la cuenta de CxC
            // *****************************************
            // $cuenta_cxc=$_SESSION['CompanyRecord']['debtorsact'];
            $SQLClient = "SELECT typeid FROM debtorsmaster WHERE debtorno='" . $DebtorNo . "'";
            $result_typeclient = DB_query($SQLClient, $db, $ErrMsg, $DbgMsg, true);
            
            if (DB_num_rows($result_typeclient) == 1) {
                $myrowtype = DB_fetch_array($result_typeclient);
                $tipocliente = $myrowtype ['typeid'];
            }
            
            if ($typeFactura == 110 ) {
  
                $cuenta_cxc = ClientAccount($tipocliente, 'gl_accountcontado', $db);
            }else{
                $cuenta_cxc = ClientAccount($tipocliente, 'gl_accountsreceivable', $db);
            }

            

            $diferenciCambiariaTotal = 0;
            $diferenciCambiariaIVA = 0;
            if($monedaSaldoANT== $moneda  aND $moneda!=$_SESSION ['CountryOfOperation']  ){

                $diferenciCambiaria=Round( abs((str_replace(",", "", $montosiniva)/$tipoCambioFacBIEN)) -  (str_replace(",", "", $montosiniva)/$rate),8) ;

                $diferenciCambiariaIVA =Round( abs($montosiniva) /$rateSaldoANT -  (str_replace(",", "", $montosiniva)/$rate),8) ;
            }elseif($monedaSaldoANT!=$_SESSION ['CountryOfOperation'] AND  $moneda!=$_SESSION ['CountryOfOperation']){

                $diferenciCambiaria=Round(abs((str_replace(",", "", $montosiniva)/$tipoCambioFacBIEN)) -  (str_replace(",", "", $montosiniva)/$rate),8) ;
                $diferenciCambiariaIVA =Round( abs($montoiva) /$rateSaldoANT -  (str_replace(",", "", $montoiva)/$rate),8) ;
            }elseif($monedaSaldoANT!=$_SESSION ['CountryOfOperation'] AND  $moneda==$_SESSION ['CountryOfOperation']){

                $diferenciCambiaria=Round(abs((str_replace(",", "", $montosiniva)/$tipoCambioFacBIEN)) -  (str_replace(",", "", $montosiniva)/$rate),8) ;
                $diferenciCambiariaIVA =Round( abs($montoiva) /$rateSaldoANT -  (str_replace(",", "", $montoiva)/$rate),8) ;
            }
            
            
            if ($montoiva != 0) {

                $SQL = "INSERT INTO gltrans (
                    type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag
                    )
                VALUES (
                    '" . $systype_doc . "',
                    '" . $transno . "',
                    '" . $DefaultDispatchDate . "',
                    '" . $PeriodNo . "',
                    '" . $taxglcode . "',
                    '" . $DebtorNo . " No. de Nota de Credito: " . $transno . " @" . $nombrecliente . " ',
                    '" . ($rmontoiva) . "',
                    '" . $tagref . "'
                )";
                
                $result = DB_query($SQL, $db);

                if( abs($ivaSaldoANT) ==0 ){
                    $SQL = "INSERT INTO gltrans (
                        type,
                        typeno,
                        trandate,
                        periodno,
                        account,
                        narrative,
                        amount,
                        tag
                        )
                    VALUES (
                        '" . $systype_doc . "',
                        '" . $transno . "',
                        '" . $DefaultDispatchDate . "',
                        '" . $PeriodNo . "',
                        '" . $taxglcodepaid . "',
                        '" . $DebtorNo . " No. de Nota de Credito: " . $transno .' @TC:' . (1 / $rateSaldoANT). " @" . $nombrecliente . " ',
                        '" . ($rmontoivaP *-1) . "',
                        '" . $tagref . "'
                    )";
                    if ($_SESSION ['UserID'] == "admin") {
                        // echo '<pre>'.$SQL;
                    }
                    $result = DB_query($SQL, $db);

                }else{
                    //$diferenciCambiaria = $diferenciCambiaria + $diferenciCambiariaIVA;
                }


                
            }
            
            // Obtiene la cuentas contables que se afectar�n
            // $cuenta_notacredito=$_SESSION['CompanyRecord']['creditnote'];

            $cuenta_notacredito= ClientAccount($tipocliente, 'gl_debtoradvances', $db);

            // *****************************************
            // Se afecta la cuenta de CxC
            // *****************************************
            
            $SQL = "INSERT INTO gltrans (
                                type,
                                typeno,
                                trandate,
                                periodno,
                                account,
                                narrative,
                                amount,
                                tag
                                )
                        VALUES (
                                '" . $systype_doc . "',
                                '" . $transno . "',
                                '" . $DefaultDispatchDate . "',
                                '" . $PeriodNo . "',
                                '" . $cuenta_cxc . "',
                                '" . $DebtorNo . " No. de Nota de Credito: " . $transno .' @TC:' . (1 / $tipoCambioFacBIEN). " @" . $nombrecliente . " ',
                                '" . ($rmonto * - 1) . "',
                                '" . $tagref . "'
                        )";
            // echo $SQL;
            if ($_SESSION ['UserID'] == "admin") {
                // echo '<pre>'.$SQL;
            }
            $result = DB_query($SQL, $db);



            if(abs($diferenciCambiaria)>0){
                if (($diferenciCambiaria) >= 0) {
                    $ctautilidadperdida = $_SESSION ['CompanyRecord'] ['exchangediffact'];
                } else {
                    $ctautilidadperdida = $_SESSION ['CompanyRecord'] ['gllink_exchangediffactutil'];
                }
                
                // $PeriodNo = GetPeriod($_SESSION['AllocCustomer']->TransDate, $db, $_SESSION['AllocCustomer']->tagref);
                
                $reference = $DebtorNo . "@UTIL/PERD CAMBIARIA@" . $diferenciCambiaria . ' @TC:' . (1 / $rateSaldoANT).' cliente:'.($nombrecliente);
                // extrae porcentaje de iva
                
                //echo "<br> diferencia cambiaria ".$diferenciCambiaria;
                
                $SQL = "INSERT INTO gltrans (type,
                                typeno,
                                trandate,
                                periodno,
                                account,
                                narrative,
                                amount,
                                tag
                                )
                            VALUES ('" . $systype_doc . "',
                                '" . $transno. "',
                                '" . $DefaultDispatchDate . "',
                                '" . $PeriodNo . "',
                                '" . $ctautilidadperdida . "',
                                '" . $reference . "',
                                '" . ($diferenciCambiaria)*(-1) . "',
                                '" . $tagref . "')";
                //echo "<br>gltrans 510: ".$SQL;
                $ErrMsg = _ ( 'CRITICAL ERROR' ) . '! ' . _ ( 'NOTE DOWN THIS ERROR AND SEEK ASSISTANCE' ) . ': ' . _ ( 'The GL entry for the difference on exchange arising out of this allocation could not be inserted because' );
                $DbgMsg = _ ( 'The following SQL to insert the GLTrans record was used' );
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

            }
            
            
            // *****************************************
            // Se afecta la cuenta de Notas de Credito
            // *****************************************
            
            $DebtorTransIDGL = DB_Last_Insert_ID($db, 'gltrans', 'counterindex');
            
            $SQL = "INSERT INTO debtortransmovs (`tagref`,`transno`,`type`,`debtorno`,`branchcode`,`origtrandate`,`trandate`,`prd`,`settled`,`reference`,`tpe`,`order_`,`rate`,`ovamount`,`ovgst`,`ovfreight`,`ovdiscount`,`diffonexch`,`alloc`,`invtext`,`shipvia`,`edisent`,`consignment`,`folio`,`ref1`,`ref2`,`currcode`,`idgltrans`,`userid`)";
            $SQL .= " VALUES (" . $tagref . ", " . $transno . "," . $systype_doc . ", '" . $DebtorNo . "', '" . $DebtorNo . "', now(), '" . $fechaini . "'," . $PeriodNo . ",'0','" . $concepto . "', '','0','" . $rate . "'," . ($montosiniva * - 1) . "," . ($montoiva * - 1) . ",'0','0','0','0','','1','0','', 'NCR','NCR','NCR','" . $moneda . "'," . $DebtorTransIDGL . ",'" . $_SESSION ['UserID'] . "')";
            $result = DB_query($SQL, $db);
            
            // afecta cuentas de notas de credito
            $SQL = "INSERT INTO gltrans (
                                type,
                                typeno,
                                trandate,
                                periodno,
                                account,
                                narrative,
                                amount,
                                tag
                                )
                        VALUES (
                                '" . $systype_doc . "',
                                '" . $transno . "',
                                '" . $DefaultDispatchDate . "',
                                '" . $PeriodNo . "',
                                '" . $cuenta_notacredito . "',
                                '" . $DebtorNo . " No. de Nota de Credito: " . $transno . " @" . $nombrecliente . " ',
                                '" . $rmontosinivaANT . "',
                                '" . $tagref . "'
                        )";
            if ($_SESSION ['UserID'] == "admin") {
                // echo '<pre>'.$SQL;
            }
            $msgexito = '<b>LA NOTA DE CREDITO SE HA GENERADO EXITOSAMENTE...';
            $result = DB_query($SQL, $db, $msgexito);
            
            if ($puntoVenta != 1) {
                prnMsg(_($msgexito), 'success');
            }
            $_SESSION['IvaManualAbonoDirectoClientes'] = 0;
            
            // imprimir datos de la nota de credito
            if (! isset($legaid) or $legaid == '' or ! isset($area) or $area == '') {
                $sql = "Select legalid,areacode from tags where tagref=" . $tagref;
                $result = DB_query($sql, $db);
                while ($myrow = DB_fetch_array($result, $db)) {
                    $legaid = $myrow ['legalid'];
                    $area = $myrow ['areacode'];
                }
            }
            
            // Consulta el rfc y clave de facturacion electronica
            $SQL = " SELECT l.taxid,l.address5,t.tagname,t.typeinvoice, l.legalname
                   FROM legalbusinessunit l, tags t
                   WHERE l.legalid=t.legalid AND tagref='" . $tagref . "'";
            
            if ($_SESSION['UserID'] == 'desarrollo')
                echo "<pre> SQL -> $SQL </pre>";

            $Result = DB_query($SQL, $db);
            if (DB_num_rows($Result) == 1) {
                $myrowtags = DB_fetch_array($Result);
                $rfc = trim($myrowtags ['taxid']);
                $keyfact = trim($myrowtags ['address5']);
                $nombre = trim($myrowtags ['tagname']);
                $tipofacturacionxtag = $myrowtags ['typeinvoice'];
                $legalname = $myrowtags ['legalname'];
                // $nombre="SERVILLANTAS DE QUERETARO S.A. DE C.V.";
            }

            if ($_SESSION['UserID'] == 'desarrollo')
                echo "<pre> tipofacturacionxtag -> $tipofacturacionxtag </pre>";

            if ($tipofacturacionxtag == 0) {
                $InvoiceNoTAG = DocumentNext($systype_doc, $tagref, $area, $legaid, $db);
            } else {
                $InvoiceNoTAG = DocumentNext(11, $tagref, $area, $legaid, $db);
            }

            if ($_SESSION['UserID'] == 'desarrollo')
                echo "<pre> InvoiceNoTAG ->". print_r($InvoiceNoTAG) ."</pre>";
            
            
            $separa = explode('|', $InvoiceNoTAG);
            $serie = $separa [1];
            $folio = $separa [0];
            // echo 'folio:'.$InvoiceNoTAG;
            
            $OrderNo = 0;
            $factelectronica = XSAInvoicingCreditdirect($transno, $OrderNo, $DebtorNo, $systype_doc, $tagref, $serie, $folio, $db);
            // Envia los datos al archivooooo
            $factelectronica = utf8_encode($factelectronica);
            
            if ($_SESSION['UserID'] == 'desarrollo') {
                echo '<pre> cadena de XSAInvoicingCreditdirect:'.$factelectronica;
                //echo htmlentities($arrayGeneracion['xml']);
            }
            
            $empresa = $keyfact . '-' . $rfc;
            $nombre = $nombre;
            $tipo = 'Notas de Credito';
            // if ($ambiente == "desarrollo") {
            //     $tipo = 'NOTA DE CREDITO';
            // }
            
            // $tipo='NOTA DE CREDITO';
            $myfile = '';
            $factelectronica = $factelectronica;
            // echo '<pre><br>'.$factelectronica;
            $param = array (
                    'in0' => $empresa,
                    'in1' => $nombre,
                    'in2' => $tipo,
                    'in3' => $myfile,
                    'in4' => $factelectronica
            );
            
            // Seccion para validar si selecciono la opcion de timbrar o no
            if ($typeFactura == 119 ) {
                $flagsendfiscal = 0;
            }else{
                $flagsendfiscal = 1;
            }
            
            $ligaN = '';

            // Si se va a timbrar el documento entra a esta condicion
            //echo "<br>tipofacturacionxtag:". $tipofacturacionxtag;
            if ($flagsendfiscal == 1) {
                if ($tipofacturacionxtag == 1) {
                    //echo "entra1";
                    try {
                        $client = new SoapClient($_SESSION ['XSA'] . "xsamanager/services/FileReceiverService?wsdl");
                        $codigo = $client->guardarDocumento($param);
                    } catch (SoapFault $exception) {
                        $errorMessage = $exception->getMessage();
                    }
                    $liga = $_SESSION ['XSA'] . "xsamanager/downloadCfdWebView?serie=" . $serie . "&folio=" . $folio . "&tipo=PDF&rfc=" . $rfc . "&key=" . $keyfact;
                    $liga = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $liga . '">' . _('Imprimir Nota de Credito') . ' (' . _('Laser') . ')' . '</a>';
                } elseif ($tipofacturacionxtag == 2) {
                    echo "entra2";
                    $arrayGeneracion = generaXML($factelectronica, 'egreso', $tagref, $serie, $folio, $DebtorTransIDND, 'NCreditoDirect', $OrderNo, $db);
                    $XMLElectronico = $arrayGeneracion ["xml"];
                    // Se agrega la generacion de xml_intermedio

                    $array = generaXMLIntermedio($factelectronica, $XMLElectronico, ($arrayGeneracion ["cadenaOriginal"]), utf8_encode($arrayGeneracion ["cantidadLetra"]), $DebtorTransIDND, $db, 13, $tagref, $systype_doc, $transno);
                    $xmlImpresion =  ( $array ["xmlImpresion"] );
                    $rfcEmisor = $array ["rfcEmisor"];
                    $fechaEmision = $array ["fechaEmision"];

                    $XMLElectronico = caracteresEspecialesFactura($XMLElectronico);
                    $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);
                    $xmlImpresion =str_replace("&", "&amp;", $xmlImpresion);
                    
                    // Almacenar XML
                    $XMLElectronico = str_replace("<?xml version='1.0' encoding='UTF-8'?>", '<?xml version="1.0" encoding="UTF-8"?>', $XMLElectronico);
                    $query = "INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal)
                                        VALUES(" . $transno . "," . $systype_doc . ",'" . $rfcEmisor . "','" . $fechaemision . "','" . utf8_decode(addslashes($XMLElectronico)) . "','" . utf8_decode(addslashes($xmlImpresion)) . "'," . $flagsendfiscal . ");";
                    $Result = DB_query($query, $db, $ErrMsg, $DbgMsg, true);
                    
                    if ($_SESSION ['Template_V6'] != '1') {
                        $ligaN = "PDFCreditDirect.php";
                    } else {
                        $ligaN = "PDFInvoice.php";
                    }
                    // $liga = "PDFCreditDirect.php";
                    $ligaN = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/' . $ligaN . SID . '?tipo=' . $systype_doc . '&area=' . $area . '&legal=' . $legaid . '&TransNo=' . $transno . '&Type=13&Tagref=' . $tagref . '">' . _('Imprimir Nota de Credito') . ' (' . _('Laser') . ')' . '</a>';
                } elseif ($tipofacturacionxtag == 3) {
                    // $XMLElectronico=generaXML($factelectronica,'egreso',$tagref,$serie,$folio,$DebtorTransIDND,'NCreditoDirect',$OrderNo,$db);
                    
                    $ligaN = "PDFNoteCreditDirectTemplate.php";
                    $ligaN = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/' . $ligaN . SID . '&tipo=' . $systype_doc . '&area=' . $area . '&legal=' . $legaid . '&TransNo=' . $transno . '&Tagref=' . $tagref . '">' . _('Imprimir Nota de Credito') . ' (' . _('Laser') . ')' . '</a>';
                } elseif ($tipofacturacionxtag == 4) {
                    $success = false;
                    $config = $_SESSION;
                    //echo "<br>---".$_SESSION['FacturaVersion'];
                    if ($_SESSION['FacturaVersion'] == "3.3") {
                        //echo "<br>generaXMLCFDI3_3:";
                        $arrayGeneracion = generaXMLCFDI3_3($factelectronica, 'egreso', $tagref, $serie, $folio, $DebtorTransIDND, 'NCreditoDirect', $OrderNo, $db);
                    } else {
                        $arrayGeneracion = generaXMLCFDI($factelectronica, 'egreso', $tagref, $serie, $folio, $DebtorTransIDND, 'NCreditoDirect', $OrderNo, $db);
                    }
                    
                    $XMLElectronico = $arrayGeneracion ["xml"];
                    $XMLElectronico = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $XMLElectronico);

                    if ($_SESSION['UserID'] == 'aenriquez' or $_SESSION['UserID'] == 'desarrollo') {
                        //echo '<pre>'.$factelectronica;
                        //echo '<br>XMLElectronico: <br><pre>'.htmlentities($XMLElectronico);
                    }
                    
                    //require_once '../../.././' .'timbradores/TimbradorFactory.php';
                    //include_once 'timbradores/TimbradorFactory.php';
                    $timbrador = TimbradorFactory::getTimbrador($config);
                    if ($timbrador != null) {
                        $timbrador->setRfcEmisor($rfc);
                        $timbrador->setDb($db);
                        $cfdi = $timbrador->timbrarDocumento($XMLElectronico);
                        $success = ($timbrador->tieneErrores() == false);
                        foreach ($timbrador->getErrores() as $error) {
                            if ($puntoVenta != 1) {
                                prnMsg($error, 'error');
                            }
                        }
                    } else {
                        if ($puntoVenta != 1) {
                            prnMsg(_('No hay un timbrador configurado en el sistema'), 'error');
                        }
                    }
                    
                    if ($success) {
                        if ($_SESSION['UserID'] == "desarrollo") {
                            echo '<br>success';
                        }
                        // leemos la informacion del cfdi en un arreglo
                        $DatosCFDI = TraeTimbreCFDI($cfdi);
                        if (strlen($DatosCFDI ['FechaTimbrado']) > 0) {
                            //$cadenatimbre = '||1.1|' . $DatosCFDI ['UUID'] . '|' . $DatosCFDI ['FechaTimbrado'] . '|' . $DatosCFDI ['selloCFD'] . '|' . $DatosCFDI ['noCertificadoSAT'] . '||';
                            $cadenatimbre = '||1.1|' . $DatosCFDI ['UUID'] . '|' . $DatosCFDI ['FechaTimbrado'] .'|' . $DatosCFDI ['RfcProvCertif']. '||' . $DatosCFDI ['SelloCFD'] . '|' . $DatosCFDI ['NoCertificadoSAT'] . '||';
                            
                            // guardamos el timbre fiscal en la base de datos para efectos de impresion de datos
                            $sql = "UPDATE debtortrans
                                  SET fechatimbrado='" . $DatosCFDI ['FechaTimbrado'] . "',
                                uuid='" . $DatosCFDI ['UUID'] . "',
                                timbre='" . $DatosCFDI ['SelloSAT'] . "',
                                cadenatimbre='" . $cadenatimbre . "'
                                where id=" . $DebtorTransIDND;
                            
                            $ErrMsg = _('El Sql que fallo fue');
                            $DbgMsg = _('No se pudo actualizar el sello y cadena del documento');
                            $Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                            $XMLElectronico = $cfdi;
                            // Guardamos el XML una vez que se agrego el timbre fiscal
                            
                            $legalname= caracteresEspecialesFactura($legalname);
                            
                            $carpeta = 'NCreditoDirect';

                            $dir = "/var/www/html" . dirname($_SERVER ['PHP_SELF']) . "/companies/" . $_SESSION ['DatabaseName'] . "/SAT/" . utf8_decode(addslashes((str_replace('.', '', str_replace(' ', '', $legalname))))) . "/XML/" . $carpeta . "/";

                            //$dir="/var/www/html/erpdistribucion/companies/".$_SESSION ['DatabaseName']."/SAT/".utf8_decode(addslashes((str_replace ( '.', '', str_replace ( ' ', '', $legalname ) ))))."/XML/NCreditoDirect/";
                            
                            if ($puntoVenta == 1) {
                                // Si es punto de venta quitar carpetas
                                $dir = str_replace('mvc/api/v1/', '', $dir);
                            }

                            
                            $nufa = $serie . $folio;
                            $mitxt = $dir . $nufa . ".xml";
                            if ($puntoVenta != 1) {
                                unlink($mitxt);
                            }
                            
                            //echo "<br>".$mitxt;
                            // Se modifico la fucion
                            $fp = fopen($mitxt, "w");
                            fwrite($fp, $XMLElectronico);
                            fclose($fp);

                            
                            $fp = fopen($mitxt . '.COPIA', "w");
                            fwrite($fp, $XMLElectronico);
                            fclose($fp);
                            //Se agrega la generacion de xml_intermedio
                            
                            $XMLElectronico_Impresion="";
                            if ($_SESSION['FacturaVersion'] == "3.3") {
                                $XMLElectronico_Impresion = generaXMLCFDI_Impresion($factelectronica, $XMLElectronico, $tagref, $db);
                            }
                            

                            if (!empty($XMLElectronico_Impresion)) {
                                $array = generaXMLIntermedio($factelectronica, $XMLElectronico_Impresion, $cadenatimbre, utf8_encode($arrayGeneracion ["cantidadLetra"]), $OrderNo, $db, 13, $tagref, $systype_doc, $transno);
                            } else {
                                $array = generaXMLIntermedio($factelectronica, $XMLElectronico, $cadenatimbre, utf8_encode($arrayGeneracion ["cantidadLetra"]), $DebtorTransIDND, $db, 13, $tagref, $systype_doc, $transno);
                            }
                            

                            //$array = generaXMLIntermedio ( $factelectronica, $XMLElectronico, $cadenatimbre, utf8_encode ( $arrayGeneracion ["cantidadLetra"] ), $DebtorTransIDND, $db, 13, $tagref, $systype_doc, $transno );

                            $xmlImpresion =  ( $array ["xmlImpresion"] );
                            $rfcEmisor = $array ["rfcEmisor"];
                            $fechaEmision = $array ["fechaEmision"];
                            $XMLElectronico = caracteresEspecialesFactura($XMLElectronico);
                            $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);
                            $xmlImpresion =str_replace("&", "&amp;", $xmlImpresion);

                            if ($_SESSION['UserID']=='desarrollo') {
                                echo '<br>XMLElectronico: <br><pre>'.htmlentities($XMLElectronico);
                                echo '<br>xmlImpresion: <br><pre>'.htmlentities($xmlImpresion);
                            }

                            // Almacenar XML//
                            $XMLElectronico = str_replace("<?xml version='1.0' encoding='UTF-8'?>", '<?xml version="1.0" encoding="UTF-8"?>', $XMLElectronico);
                            $query = "INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal)
                                        VALUES(" . $transno . "," . $systype_doc . ",'" . $rfcEmisor . "','" . $fechaemision . "','" . utf8_decode(addslashes($XMLElectronico)) . "','" . utf8_decode(addslashes($xmlImpresion)) . "'," . $flagsendfiscal . ");";
                            $Result = DB_query($query, $db, $ErrMsg, $DbgMsg, true);
                            
                            if ($_SESSION ['Template_V6'] != '1') {
                                $ligaN = "PDFCreditDirect.php";
                            } else {
                                $ligaN = "PDFInvoice.php";
                            }
                            
                            if ($puntoVenta != 1) {
                                $ligaN = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/' . $ligaN . SID . '?Type=' . $systype_doc . '&area=' . $area . '&legal=' . $legaid . '&TransNo=' . $transno . '&Tagref=' . $tagref . '">' . _('Imprimir Nota de Credito') . ' (' . _('Laser') . ')' . '</a>';
                            }
                        } else {
                            prnMsg(_('No fue posible realizar el timbrado del documento, verifique con el administrador; el numero de error es:') . $cfdi, 'error');
                            // exit;
                        }
                    }
                } else {
                    $ligaN = GetUrlToPrintNu($tagref, $area, $legaid, $systype_doc, $db);
                    $ligaN = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/' . $ligaN . SID . '&tipo=' . $systype_doc . '&area=' . $area . '&legal=' . $legaid . '&TransNo=' . $transno . '&Tagref=' . $tagref . '">' . _('Imprimir Nota de Credito') . ' (' . _('Laser') . ')' . '</a>';
                }
            } else {
                //echo "<br> flagsendfiscal";
                // Documento No Fiscal
                $arrayGeneracion = generaXMLCFDI($factelectronica, 'egreso', $tagref, $serie, $folio, $DebtorTransIDND, 'NCreditoDirect', $OrderNo, $db);
                $XMLElectronico = $arrayGeneracion ["xml"];
                
                // Se agrega la generacion de xml_intermedio
                $array = generaXMLIntermedio($factelectronica, $XMLElectronico, $arrayGeneracion ["cadenaOriginal"], utf8_encode($arrayGeneracion ["cantidadLetra"]), $DebtorTransIDND, $db, 13, $tagref, $systype_doc, $transno);
                $xmlImpresion = utf8_decode($array ["xmlImpresion"]);
                $rfcEmisor = $array ["rfcEmisor"];
                $fechaEmision = $array ["fechaEmision"];
                $XMLElectronico = caracteresEspecialesFactura($XMLElectronico);
                $xmlImpresion = caracteresEspecialesFactura($xmlImpresion);
                $xmlImpresion =str_replace("&", "&amp;", $xmlImpresion);

                
                // Insertar el registro en la tabla de XMLS
                $query = "INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal)
                            VALUES(" . $transno . "," . $systype_doc . ",'" . $rfcEmisor . "','" . $fechaemision . "','" . utf8_decode(addslashes($XMLElectronico)) . "','" . utf8_decode(addslashes($xmlImpresion)) . "'," . $flagsendfiscal . ");";
                
                $Result = DB_query($query, $db, $ErrMsg, $DbgMsg, true);
                
                if ($_SESSION ['Template_V6'] != '1') {
                    $ligaN = "PDFCreditDirect.php";
                } else {
                    $ligaN = "PDFInvoice.php";
                }
                            
                $ligaN = '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/' . $ligaN . SID . '?Type=' . $systype_doc . '&area=' . $area . '&legal=' . $legaid . '&TransNo=' . $transno . '&Tagref=' . $tagref . '">' . _('Imprimir Nota de Credito') . ' (' . _('Laser') . ')' . '</a>';
                    
                /*$PrintDispatchNote = $rootpath . '/' . $liga . '?OrderNo=' . $OrderNo . '&TransNo=' . $BatchNo . '&Type=' . $tipodefacturacion;
                    
                echo '<p class="page_title_text">';
                echo '<img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Recibo ' ) . '" alt="">' . ' ';
                echo '<a href="' . $PrintDispatchNote . '" target="_blank">';
                echo _ ( 'Imprimir Recibo PDF ' ) . '</a></p>';*/
            } // Fin de condicion para timbrar el documento o no
            
            // Actualizar el documento para folio
            $SQL = "UPDATE debtortrans
                    SET folio='" . $serie . '|' . $folio . "',
                      flagfiscal= '" . $flagsendfiscal . "' 
                    WHERE transno=" . $transno . " and type=" . $systype_doc;
            
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
            $DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            $Result = DB_Txn_Commit($db);

            if ($puntoVenta != 1) {
                echo '<p><div align="center">';
                echo $ligaN;
                echo '</div>';
            }

            if (function_exists("updateGlTransAccountingInfo")) {
                $valued = updateGlTransAccountingInfo($systype_doc, $transno, $db);
            }
            
            if ($puntoVenta != 1) {
                echo '<p><div align="center">';
                echo '<a href="ReportCustomerInqueryV3.php?CustomerID=' . $DebtorNo . '">';
                echo '<font size=2 face="arial"><b>';
                echo _('IR AL ESTADO DE CUENTA DEL CLIENTE');
                echo '</b></font>';
                echo '</a>';
                echo '</div>';
            }
    }
        
        // ***************************************************************************************************
        // ***************************************************************************************************
        // ***************************************************************************************************
}

// Se crea la función para identificar si trae iva o no ambos documentos debido a que cuando tiene pagares no esta considerando correctamente el anticipo
function fnCalculaTotalAnticipo($tipoCambioFac = 0,$montoAnticipo=0, $monedaFactura, $totalFactura,  $tieneIVA, $ivaFactura, $db){
    
    //echo "<br> AnticipoIVA: ".$tieneIVA;
    $tipocambio = $tipoCambioFac;
    $montoAnticipo = $montoAnticipo;
    $monto = $montoAnticipo;
    $moneda  = $monedaFactura;
    $tieneIVA =  $tieneIVA;
    $TotalFactura = $totalFactura;


    if($tieneIVA == '1'){
        $taxCat = 4;
    }else{
        $taxCat = 2;
    }

    if (isset($taxCat) and $taxCat != "") {
        $sqliva = "SELECT taxrate,taxglcode,taxglcodepaid
                FROM taxauthrates, taxauthorities
                WHERE taxauthrates.taxauthority=taxauthorities.taxid
                AND taxauthrates.taxcatid =" . $taxCat;

        //echo "<br> SQL IVA".$sqliva;

        $result = DB_query($sqliva, $db);
        $myrow = DB_fetch_row($result);
        $taxrate = $myrow [0];
        $taxglcode = $myrow [1];
        $taxglcodepaid = $myrow [2];
    }

    // calcula iva y desglosa de iva
    // $montosiniva = $monto / (1 + $taxrate);
    // $montoiva = $monto - $montosiniva;

        $mntIVA = $_POST['mntIVA'];

    // calcula iva y desglosa de iva
    if (($mntIVA == 0 || empty($mntIVA))) {
        //Iva del categoria impuestos
        $montosiniva = $monto / (1 + $taxrate);
        $montoiva = $monto - $montosiniva;
    }else{
        //Iva agregado manual
        $_SESSION['IvaManualAbonoDirectoClientes'] = 1;
        //
        $montosiniva = $monto - $mntIVA;
        //$montosiniva = $monto;
        $montoiva = $mntIVA;
    } 
    // se valida ya que debe de acuerdo al iva de l factura
    //echo "<br> ivaFactura2: ".$ivaFactura;
    if( (Round($TotalFactura,2) -  Round($monto,2) < -.01) AND $tieneIVA ==1 AND $ivaFactura == 0){

       $monto = $montosiniva;
       $montoiva  = $montosiniva - ($montosiniva/1.16);
       $montosiniva  = ($montosiniva/1.16);
       $montoAnticipo = $monto;

    }
    $totalFacA=$montosiniva + $montoiva;
    return $totalFacA;
}

?>