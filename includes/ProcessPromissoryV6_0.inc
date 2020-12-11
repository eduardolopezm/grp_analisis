<?php
if (isset($_POST['PaymentTerms'])){
		$terminopago=$_POST['PaymentTerms'];
		$SQL="SELECT * FROM paymentterms WHERE termsindicator='".$terminopago."'";
		$result_term = DB_query($SQL,$db);
		if (DB_num_rows($result_term)==1) {
			$myrowterm = DB_fetch_array($result_term);
			$daysbefore=$myrowterm['daysbeforedue'];
			$generapagare=$myrowterm['generateCreditNote'];
			$dayinfollowingmonth=$myrowterm['dayinfollowingmonth'];
			$numberOfPayments=$myrowterm['numberOfPayments'];
			$fechatemporada=$myrowterm['fixdate'];
			$daygrace=$myrowterm['daygrace'];
			$fechavencimiento=date("Y/m/d");
			// si no genera pagares solo actualiza factura de credito a nuevo vencimiento
			if ($generapagare==0){
				if ($daysbefore>1){
					$diasvencimiento=$daysbefore;
					if ($dayinfollowingmonth==0){
						$fechavencimiento=date("Y/m/d");
						$fechavence = dateadd_dos($fechavencimiento,$diasvencimiento,0,0,0,0,0);
					}else {
						$fechavencimiento=date("Y/m/d");
						$fechavence = dateadd_dos($fechavencimiento,$diasvencimiento,0,0,0,0,0);
						$separa = explode('/',$fechavence);
						$mesvence = $separa[1];
						$aniovence = $separa[0];
						$diavence="01";
						$fechavence=$aniovence.'/'.$mesvence.'/'.$diavence;
					}
					if(strlen($fechatemporada)>0){
						$fechavence=$fechatemporada;
					}
					
					$SQL="UPDATE debtortrans
					      SET trandate='" . $fechavence . "'
					      WHERE transno=".$InvoiceNo." and type=".$tipodefacturacion;
					$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
					$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
				
			}elseif ($daysbefore>1){
				//saldar cuenta por cobrar
				$pagofact=$_SESSION['Items'.$identifier]->total;
				$total_saldar=($TaxTotal+$_SESSION['Items'.$identifier]->total+$totretencion)-$anticipo;
				$TaxPagarepago=($anticipo*$TaxTotal)/($pagofact+$TaxTotal);
				$TaxPagare=($TaxTotal-$TaxPagarepago);
				$TAXGRAL=$TaxPagare;
				$SQL="UPDATE debtortrans
				      SET alloc=alloc+" . $total_saldar . "
				      WHERE transno=".$InvoiceNo." and type=".$tipodefacturacion;
				$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
				$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				//$cuentapagare=$_SESSION['CompanyRecord']['gllink_notesdebtors'];
				$tipocliente=ExtractTypeClient($_SESSION['Items'.$identifier]->DebtorNo,$db);
				$cuentapagare=ClientAccount($tipocliente,'gl_notesreceivable',$db);//$_SESSION['CompanyRecord']['gllink_notesdebtors'];
				$cuentacargo=ClientAccount($tipocliente,'gl_accountsreceivable',$db);
				$diasvence=0;
				for ($x=1;$x<=$numberOfPayments;$x++){
					//trae de tabla de vencimientos los dias que corresponden a nuevo pago
					$SQL='Select * from paymenttermbynumber where numberpay='.$x .' 
							and termsindicator="'.$terminopago.'"';
					$result = DB_query($SQL, $db);
					//echo '<pre><br>entraa'.$SQL;
					if (DB_num_rows($result)>0){
						$myrowpay = DB_fetch_array($result);
						$numberOfPayment=$i;
						$daysbefore=$myrowpay['daysbeforedue'];
						$daygrace=$myrowpay['daygrace'];
						$cashdiscount=$myrowpay['cashdiscount'];
						//echo 'entraa';
						$bandera=true;
						
						
					}else{
						$bandera=false;
						$numberOfPayment=$i;
						$daysbefore=$daysbefore;
						$cashdiscount=$_POST['descuentopago'];
						//$daygrace=$myrowpay['daygrace'];
					
					}
					
					if ($dayinfollowingmonth==0){
						$fechavencimiento=date("Y/m/d");
						if($bandera==false){
							$diasvencimiento=($daysbefore*$x);
						}else{
							$diasvencimiento=$diasvence+($daysbefore);
						}
						$fechavence = dateadd_dos($fechavencimiento,$diasvencimiento,0,0,0,0,0);
					}else {
						$fechavencimiento=date("Y/m/d");
						//$diasvencimiento=($daysbefore*$x);
						if($bandera==false){
							$diasvencimiento=($daysbefore*$x);
						}else{
							$diasvencimiento=$diasvence+($daysbefore);
						}
						$fechavence = dateadd_dos($fechavencimiento,$diasvencimiento,0,0,0,0,0);
						$separa = explode('/',$fechavence);
						$mesvence = $separa[1];
						$aniovence = $separa[0];
						$diavence="01";
						$fechavence=$aniovence.'/'.$mesvence.'/'.$diavence;
					}
					$diasvence=$diasvence+$daysbefore;
					
					$TaxPagare=($TaxTotal-$TaxPagarepago)/$numberOfPayments;
					$totalpagarexpagar=($total_saldar-$TAXGRAL);
					$totalpagare=$totalpagarexpagar/$numberOfPayments;
					
					$PagareNO = GetNextTransNo(70, $db);

/*					$codigoref=$_SESSION['Items'.$identifier]->DebtorNo;
				        $codigoref=strtoupper($codigoref);
		                        $codigoref=add_cerosstring($codigoref,7);
				        $tipodefactura=add_cerosstring(70,3);
		                        $transfact=add_cerosstring($PagareNO,6);
				        $tagfact=add_cerosstring($_SESSION['Tagref'],3);		
				        $cuentareferenciada=$codigoref.$tipodefactura.$transfact.$tagfact;
				        $cuentaref = strtoupper($cuentareferenciada.GeneraCuentaReferenciada($db,$cuentareferenciada));
*/

				        $SQL=" SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid
						FROM legalbusinessunit l, tags t
						WHERE l.legalid=t.legalid AND tagref='".$_SESSION['Tagref']."'";
				        $Result= DB_query($SQL,$db);
				        if (DB_num_rows($Result)==1) {
						$myrowtags = DB_fetch_array($Result);
						$rfc=trim($myrowtags['taxid']);
						$keyfact=$myrowtags['address5'];
						$nombre=$myrowtags['tagname'];
						$area=$myrowtags['areacode'];
						$legaid=$myrowtags['legalid'];
				        }				
				        
					// Genera transaccion de Pagare 
					$SQL = "INSERT INTO debtortrans (
							transno,
							type,
							debtorno,
							branchcode,
							trandate,
							prd,
							reference,
							tpe,
							order_,
							ovamount,
							ovgst,
							ovfreight,
							rate,
							invtext,
							shipvia,
							consignment,
							tagref,
							currcode,
							discountpercentpayment,
							discountpercent,
							ref1,
							origtrandate
							)
						VALUES (
							". $PagareNO . ",
							70,
							'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
							'" . $_SESSION['Items'.$identifier]->Branch . "',
							'" . $fechavence . "',
							" . $PeriodNo . ",
							" . $InvoiceNo . ",
							'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
							" .  $InvoiceNo . ",
							" . $totalpagare . ",
							" . $TaxPagare . ",
							0,
							" . $_SESSION['Items'.$identifier]->CurrencyRate . ",
							'" . $_POST['InvoiceText'] .' De la Factura '.$InvoiceNo . "',
							" . $_POST['shipvia'] . ",
							'"  . $_SESSION['Items'.$identifier]->OrderNo  . "',
							" . $_SESSION['Items'.$identifier]->Tagref .",
							'"  . $_SESSION['Items'.$identifier]->CurrAbrev  . "',
							'"  . $cashdiscount  . "',
							'"  . $daygrace  . "',
							'"  . $cuentaref  . "',
						        '"  . $fechaemision . "'
						)";
					$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro del pagare para el cliente no se realizo');
					$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
					// inserta el abono al documento de cargo en cargosabonos
					$pagareid = DB_Last_Insert_ID($db,'debtortrans','id');
					
					
					$codigoref=strtoupper($_SESSION['Items'.$identifier]->CustRef);
				        $tipoarea=add_cerosstring($area,2);
				        $translegal=add_cerosstring($legaid,2);
				        $cuentareferenciada=$translegal.$tipoarea.$codigoref;
				        //$cuentaref = strtoupper($cuentareferenciada.GeneraCuentaReferenciada($db,$cuentareferenciada));
				        $sql="Select * from bancosreferencia where active=1";
				        $result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				        if (DB_num_rows($result)!=0)
				        {
						while ($myrowcuenta = DB_fetch_array($result,$db)){
							$bankid=$myrowcuenta['bancoid'];
							// genera digito verificador
							$cuentaref = strtoupper($cuentareferenciada.GeneraCuentaReferenciada($db,$cuentareferenciada,$bankid));
							// inserta en tabla de referencias bancarias
							$insertarefe=InsertaCuentaBank($cuentaref,$pagareid,$bankid,$db);				
						}
				        }
					
					$SQL = "INSERT INTO custallocns(
								amt,
								datealloc,
								transid_allocfrom,
								transid_allocto
						)VALUES (
								'". ($totalpagare+$TaxPagare) . "',
								'" . $fechaemision . "',
								" . $pagareid . ",
								" . $DebtorTransID . "
						        )";
					$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro del pagare para el cliente no se realizo');
					$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
					// Abono a Cuenta por Cobrar / a documentos por cobrar				
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
						70,
						" . $PagareNO . ",
						'" . $DefaultDispatchDate . "',
						" . $PeriodNo . ",
						" . $cuentacargo . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo. ' No de Factura: '. $InvoiceNo . "',
						" . (($totalpagare+$TaxPagare) *-1)/$_SESSION['Items'.$identifier]->CurrencyRate . ",
						" . $_SESSION['Items'.$identifier]->Tagref . "
					)";
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se ha registrado el abono a la cuenta por cobrar, por que');
					$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					// MOVIMIENTOS EN DOCUMENTOS POR COBRAR					
					// Inserta el movimiento contable para el cargo de Documentos por cobrar DE TOTAL DE VENTA
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
						70,
						" . $PagareNO . ",
						'" . $DefaultDispatchDate . "',
						" . $PeriodNo . ",
						" . $cuentapagare . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . ' No de Factura: '. $InvoiceNo .  "',
						" . ($totalpagare+$TaxPagare)/$_SESSION['Items'.$identifier]->CurrencyRate . ",
						" . $_SESSION['Items'.$identifier]->Tagref . "
					)";
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El total del pagare no se ha insertado, por que');
					$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					// ***************************************** inserta en movimientos de debtortrasnmov para estado de cuenta*********************************************
					$GLTransID = DB_Last_Insert_ID($db,'gltrans','counterindex');
					// Insertar en debtortransmov para consulta de estado de cuenta
					$SQL = "INSERT INTO debtortransmovs (
							transno,
							type,
							debtorno,
							branchcode,
							trandate,
							prd,
							reference,
							tpe,
							order_,
							ovamount,
							ovgst,
							ovfreight,
							rate,
							invtext,
							
							consignment,
							tagref,
							currcode,
							idgltrans,
							origtrandate
							)
						VALUES (
							". $PagareNO . ",
							70,
							'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
							'" . $_SESSION['Items'.$identifier]->Branch . "',
							'" . $fechavence . "',
							" . $PeriodNo . ",
							" . $InvoiceNo . ",
							'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
							" .  $InvoiceNo . ",
							" . $totalpagare . ",
							" . $TaxPagare . ",
							0,
							" .$_SESSION['Items'.$identifier]->CurrencyRate . ",
							'" . $_POST['InvoiceText'] .' De la Factura '.$InvoiceNo . "',
							'"  . $_SESSION['Items'.$identifier]->OrderNo  . "',
							" . $_SESSION['Items'.$identifier]->Tagref .",
							'"  . $_SESSION['Items'.$identifier]->CurrAbrev  . "',
							" . $GLTransID .",
							'"  . $fechaemision . "'
						)";
					$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro del pagare para el cliente no se realizo');
					$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}// Fin de daysbefore
			}// Fin de paymentterms
		}// Fin de For 
	}// fin de post terms
?>