<?php

$entracompra=false;
$indice=0;
foreach ($_SESSION['PO']->LineItems as $TrfLine) {
	if ($TrfLine->ReceiveQty !=0 AND $TrfLine->ReceiveQty!='' AND isset($TrfLine->ReceiveQty)) {
	if($entracompra==false){
			$entracompra=true;
			//datos para la orden de compra
			$SQL="select * from locations where loccode='".$almacenCompra."'";
			$Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$CompraAlmacenRow = DB_fetch_row($Resultalmacen);
			$DelAdd1 = $CompraAlmacenRow[2];
			$DelAdd2 = $CompraAlmacenRow[3];
			$DelAdd3 = $CompraAlmacenRow[4];
			$DelAdd4 = $CompraAlmacenRow[5];
			$DelAdd5 = $CompraAlmacenRow[6];
			$DelAdd6 = $CompraAlmacenRow[7];
			
			
			//$ProveedorCompra=$_SESSION['SupplierDefault'];
			$typesupplier=10;
			// inserta la compra para el que recibe
			 $sql = "INSERT INTO purchorders (
						supplierno,
						comments,
						orddate,
						rate,
						initiator,
						requisitionno,
						intostocklocation,
						deladd1,
						deladd2,
						deladd3,
						deladd4,
						deladd5,
						deladd6,
						version,
						realorderno,
						revised,
						status,
						stat_comment,
						deliverydate,
						tagref
						)
					VALUES(
						'" . $ProveedorCompra. "',
						'" . 'Compra automatica generada por la transferencia: '. $_SESSION['Transfer']->TrfID.' que realizo el usuario '.$_SESSION['UserID'] . "',
						now(),
						'',
						'" . $_SESSION['UserID'] . "',
						0,
						'" . $almacenCompra. "',
						'" . $DelAdd1 . "',
						'" . $DelAdd2 . "',
						'" . $DelAdd3 . "',
						'" . $DelAdd4 . "',
						'" . $DelAdd5 . "',
						'" . $DelAdd6 . "',
						'0',			
						'" . $_SESSION['ExistingOrder']. "',
						now(),";
					$sql.="'" . 'Completed'. "',";
					$sql.="		'Compra automatica',
						now(),
						".$unidadCompra."
					)";
					
			$ErrMsg =  _('The purchase order header record could not be inserted into the database because');
			$DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
			$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$OrderNoCompra = DB_Last_Insert_ID($db,'purchorders','orderno');
			
			//$DebtorNo='0002_02';//$_SESSION['ClaveFacturaTransferencia'];
			$SQL="SELECT areacode,tagdebtorno from tags where tagref=".$unidadVenta;
			$ResultVenta = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$VentaRow = DB_fetch_row($ResultVenta);
			$Area=$VentaRow[0];
			$DefaultSalesType='PL';
			$DebtorNo=$VentaRow[1];
			
			$Comments="Orden de venta directa de transferencias del proveedor".$_SESSION['PO']->SupplierID.' - '.$_SESSION['PO']->SupplierName.' Con la Compra: '.$_SESSION['PO']->OrderNo;
			$SQL="SELECT brname,braddress1,braddress2,braddress3,braddress4,braddress5,
				braddress6,currcode,paymentterms,phoneno,email,salesman
				FROM custbranch inner join debtorsmaster on custbranch.debtorno=debtorsmaster.debtorno
			where debtorsmaster.debtorno='".$DebtorNo."'";
			$ResultVenta = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$VentaRow = DB_fetch_row($ResultVenta);
			$DeliverTo=$VentaRow[0];
			$DelAdd1=$VentaRow[1];
			$DelAdd2=$VentaRow[2];
			$DelAdd3=$VentaRow[3];
			$DelAdd4=$VentaRow[4];
			$DelAdd5=$VentaRow[5];
			$DelAdd6=$VentaRow[6];
			$CurrAbrev=$VentaRow[7];
			$Rate=1;
			$terminopago=10;//$VentaRow[8];
			$PhoneNo=$VentaRow[9];
			$Email=$VentaRow[10];
			$Vendedor=$VentaRow[11];
			
			// inserta la orden de venta para el que envia
			$OrderNo = GetNextTransNo(30, $db);
			$HeaderSQL = 'INSERT INTO salesorders ( orderno,
							debtorno,
							branchcode,
							customerref,
							comments,
							orddate,
							ordertype,
							shipvia,
							deliverto,
							deladd1,
							deladd2,
							deladd3,
							deladd4,
							deladd5,
							deladd6,
							contactphone,
							contactemail,
							freightcost,
							fromstkloc,
							deliverydate,
							quotedate,
							confirmeddate,
							quotation,
							deliverblind,
							salesman,
							tagref,
							taxtotal,
							totaltaxret,
							currcode,
							paytermsindicator,
							UserRegister
							
						       )
						VALUES (
							'. $OrderNo . ',
							'."'" . $DebtorNo . "'".',
							'."'" . $DebtorNo . "'".',
							'."'". DB_escape_string($CustRef) ."'".',
							'."'". DB_escape_string($Comments) ."'".',
							'."'" . Date("Y-m-d H:i") . "'".',
							'."'" . $DefaultSalesType . "'".',
							1,
							'."'". DB_escape_string($DeliverTo) . "'".',
							'."'" . DB_escape_string($DelAdd1) . "'".',
							'."'" . DB_escape_string($DelAdd2) . "'".',
							'."'" . DB_escape_string($DelAdd3) . "'".',
							'."'" . DB_escape_string($DelAdd4) . "'".',
							'."'" . DB_escape_string($DelAdd5) . "'".',
							'."'" . DB_escape_string($DelAdd6) . "'".',
							'."'" . DB_escape_string($PhoneNo) . "'".',
							'."'" . DB_escape_string($Email) . "'".',
							0,
							'."'" . $almacenVenta ."'".',
							'."'" . Date('Y-m-d') . "'".',
							'."'" . Date('Y-m-d') . "'".',
							'."'" . Date('Y-m-d') . "'".',
							 4,
							"",
							' ."'" . $Vendedor ."'" .',
							' . $unidadVenta .',
							0,
							0,
							' ."'" . $CurrAbrev . "'" .',
							' ."'" . $terminopago . "'" .',
							' ."'" . $_SESSION['UserID'] . "'" .'
							)';
			$ErrMsg = _('La orden no se puede agregar, por que');
			$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);
			
			 $sqlCompraInicial = "INSERT INTO purchorderdetails (
				orderno,
				itemcode,
				deliverydate,
				itemdescription,
				glcode,
				unitprice,
				quantityord,
				shiptref,
				jobref,
				itemno,
				uom,
				suppliers_partno,
				subtotal_amount,
				package,
				pcunit,
				nw,
				gw,
				cuft,
				total_quantity,
				total_amount,
				discountpercent1,
				discountpercent2,
				discountpercent3,
				narrative,
				justification,
				refundpercent,
				quantityrecd,
				qtyinvoiced
				)VALUES (";
			
			$StartOf_LineItemsSQL = 'INSERT INTO salesorderdetails (
					orderlineno,
					orderno,
					stkcode,
					unitprice,
					quantity,
					discountpercent,
					discountpercent1,
					discountpercent2,
					narrative,
					poline,
					itemdue,
					fromstkloc,
					warranty,
					servicestatus,
					refundpercent,
					quantitydispatched
					)
			VALUES (';
			
			$SQLClient="SELECT typeid FROM debtorsmaster WHERE debtorno='".$DebtorNo."'";
			$result_typeclient = DB_query($SQLClient,$db,$ErrMsg,$DbgMsg,true);
			if (DB_num_rows($result_typeclient)==1) {
				$myrowtype = DB_fetch_array($result_typeclient);
				$tipocliente=$myrowtype['typeid'];
			}
			//echo 'sss;'.$tipocliente;
			$terminopago='01';
			$SQL="SELECT * FROM paymentterms WHERE termsindicator='".$terminopago."'";
			$result_term = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			if (DB_num_rows($result_term)==1) {
				//$myrowterm = DB_fetch_row($result_term);
				$myrowterm = DB_fetch_array($result_term);
				$generapagare=$myrowterm['generateCreditNote'];
				$numberOfPayments=$myrowterm['numberOfPayments'];
				$_SESSION['Paypromissory']=$myrowterm['automatic'];
				$pagares=$myrowterm['numberOfPayments'];
				$daysbeforevencimiento=$myrowterm['daysbeforedue'];
				if ($generapagare==1){
					//genero pagares, tipo de venta credito
					if ($numberOfPayments==0){
						$tipodefacturacion=110;
						$cuentacargo=ClientAccount($tipocliente,'gl_accountcontado',$db);//$_SESSION['CompanyRecord']['gllink_Invoice'];
					}else{
						$tipodefacturacion=10;
						$cuentacargo=ClientAccount($tipocliente,'gl_accountsreceivable',$db);//$_SESSION['CompanyRecord']['debtorsact'];
					}
				} else {
					if($numberOfPayments==0){
						// no tiene vencimientos, tipo de venta de contado
						$tipodefacturacion=110;
						// cuenta de cargo es facturas de contado
						//$cuentacargo=$_SESSION['CompanyRecord']['gllink_Invoice'];
						$cuentacargo=ClientAccount($tipocliente,'gl_accountcontado',$db);//$_SESSION['CompanyRecord']['gllink_Invoice'];
					}else{
						// tiene vencimientos, tipo de venta de credito	
						$tipodefacturacion=10;
						// cuenta de cargo, es cuentas por cobrar
						$cuentacargo=ClientAccount($tipocliente,'gl_accountsreceivable',$db);//$_SESSION['CompanyRecord']['debtorsact'];
					}
				}
			}
			//identificador de la factura
			$DefaultSalesType='PL';
			//echo 'tipo;'.$tipodefacturacion;
			$InvoiceNo = GetNextTransNo($tipodefacturacion, $db);
			
			//extrae el numero de transaccion para compras
			$GRN = GetNextTransNo(25, $db);
			$PeriodNo= GetPeriod(Date('Y-m-d'), $db);
			$InvoiceNoCompra = GetNextTransNo(20, $db);
			
		}
		$avgcost = ($TrfLine->Price / $_SESSION['PO']->ExRate);;
		$avgcost = $avgcost - ($avgcost*($TrfLine->Desc1/100));
		$avgcost = $avgcost - ($avgcost*($TrfLine->Desc2/100));
		$avgcost = $avgcost - ($avgcost*($TrfLine->Desc3/100));
		// movimiento de la factura de compra
		$costofactproveedoruno=$avgcost;
		$EstimatedAvgCost=$avgcost;
		 //extrae referencia de orden de compra
		$sql = "SELECT
			    stockmaster.description,
			    purchdata.suppliers_partno,
			    1 as pkg_type, /*stockmaster.pkg_type,*/
			    stockmaster.units,
			    stockmaster.netweight,		
			    stockmaster.kgs,
			    stockmaster.volume,
			    stockcategory.stockact,
			    stockmaster.manufacturer
			    FROM purchdata INNER JOIN stockmaster
			    ON purchdata.stockid=stockmaster.stockid,stockcategory
			    
			    WHERE stockcategory.categoryid = stockmaster.categoryid
				AND purchdata.stockid='" . $TrfLine->StockID . "'
				AND purchdata.supplierno='".$ProveedorCompra."'";
		    
		$ErrMsg = _('El producto no tiene proveedor configurado') . ': ' . $TrfLine->StockID  . ' ' . _('no hay resultados');
		$DbgMsg = _('El SQL utilizado es');
		$ResultProveedorcompra =DB_query($sql,$db,$ErrMsg,$DbgMsg);
		$myrow = DB_fetch_row($ResultProveedorcompra);
		
		$_POST['ItemDescription'] = $myrow[0];
		$_POST['suppliers_partno'] = $myrow[1];
		$_POST['package'] = 1;//$myrow[2];
		$_POST['uom'] = $myrow[3];
		$_POST['nw'] = $myrow[4];
		$_POST['gw'] = $myrow[5];
		$_POST['cuft'] = $myrow[6];
		$CompraStockidglcode=$myrow[7];
		$Devolucion=0;
		// inserta el detalle de la compra
		$sqlCompra = $sqlCompraInicial
		. $OrderNoCompra . ",'" . $TrfLine->StockID . "',now(),'" . $_POST['ItemDescription'] . "','" . $CompraStockidglcode . "',
		".$EstimatedAvgCost."," . ($TrfLine->ReceiveQty). ",0,0,'" . $_POST['ItemDescription'] . "','" . $_POST['uom'] . "','" . $_POST['suppliers_partno'] . "',
		'" . $POLine->subtotal_amount . "','" . $_POST['package']. "','" . $POLine->pcunit . "','" . $_POST['nw'] . "','" . $_POST['gw'] . "',
		'" . $_POST['cuft'] . "','" . ($cantidadsol) . "','0','0','0','0','" . trim(DB_escape_string((htmlspecialchars_decode($Comments,ENT_NOQUOTES)))) . "',
		'Orden de compra automatica','" . ($Devolucion) . "','" . ($TrfLine->ReceiveQty) . "','" . ($TrfLine->ReceiveQty). "')";
		$ErrMsg =_('No se pudo insertar el detalle de la orden de compra');
		$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
		$Result =DB_query($sqlCompra,$db,$ErrMsg,$DbgMsg);
		$podetailitem = DB_Last_Insert_ID($db,'purchorderdetails','podetailitem');
		//inserta la recepcion de la orden de compra
		$SQL = "INSERT INTO grns (grnbatch,
				podetailitem,
				itemcode,
				itemdescription,
				deliverydate,
				qtyrecd,
				supplierid,
				stdcostunit)
		VALUES (" . $GRN . ",
			" . $podetailitem . ",
			'" . $TrfLine->StockID . "',
			'" . $_POST['ItemDescription'] . "',
			'" . Date('Y-m-d') . "',
			" . $TrfLine->ReceiveQty . ",
			'" . $ProveedorCompra . "',
			" . $EstimatedAvgCost . ')';
		$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la transaccion');
		$DbgMsg =  _('El SQL utilizado es');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		// inserta el movimiento de la orden de compra
		$SQL = "INSERT INTO stockmoves (stockid,
						type,
						transno,
						loccode,
						trandate,
						price,
						prd,
						reference,
						qty,
						standardcost,
						newqoh,
						discountpercent,
						discountpercent1,
						discountpercent2,
						tagref,
						avgcost
						)
			VALUES ('" . $TrfLine->StockID. "',
				25,
				" . $GRN . ", '" . $almacenCompra . "',
				'" . Date('Y-m-d') . "',
				" . $EstimatedAvgCost . ",
				" . $PeriodNo . ",
				'" . $ProveedorCompra . " (" . $SupplierName . ") - " .$OrderNoCompra. "',
				" . $TrfLine->ReceiveQty . ",
				" . $EstimatedAvgCost . ",
				" . ($TrfLine->ReceiveQty) . ",
				0,
				0,
				0,
				" . $unidadCompra . ",
				'" . $EstimatedAvgCost . "')";
		//echo "<br>5.-" . $SQL;
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realice el movimiento de inventario');
		$DbgMsg =  _('El SQL utilizado es');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		
		$sql = "SELECT stockcategory.stockact,
			     stockmaster.description
			    FROM stockmaster,stockcategory
			    WHERE stockcategory.categoryid = stockmaster.categoryid
				AND stockmaster.stockid='" . $TrfLine->StockID . "'";
		$ErrMsg = _('El producto no tiene proveedor configurado') . ': ' . $TrfLine->StockID  . ' ' . _('no hay resultados');
		$DbgMsg = _('El SQL utilizado es');
		$ResultProveedorcompra =DB_query($sql,$db,$ErrMsg,$DbgMsg);
		$myrow = DB_fetch_row($ResultProveedorcompra);
		$GLCode = $myrow[0];
		$_POST['ItemDescription'] = $myrow[1];
		
		//inserta el movimiento contable de la recepcion
		$SQL = "INSERT INTO gltrans (type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					tag)
			VALUES (25,
				" . $GRN . ",
				'" .Date('Y-m-d') . "',
				" . $PeriodNo . ",
				" . $GLCode. ",
				'PO: " . $OrderNoCompra . " " .$ProveedorCompra . " - " . $TrfLine->StockID . " - " . $_POST['ItemDescription'] . " x " . $TrfLine->ReceiveQty . " @ " . number_format($EstimatedAvgCost,2) . "',
				" . $EstimatedAvgCost * $TrfLine->ReceiveQty . ",
				" . $unidadCompra . ")";
			//echo "<br>6.-" . $SQL;
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de las cuentas contables para la orden de compra no se realizo');
		$DbgMsg = _('El SQL utilizado es');
		$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
		$SQL = "INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag)
				VALUES (25,
					" . $GRN . ",
					'" . Date('Y-m-d') . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']['grnact'] . ", '" .
					_('PO') . ': ' . $OrderNoCompra . ' ' . $ProveedorCompra . ' - ' . $TrfLine->StockID . ' - ' . $_POST['ItemDescription']  . ' x ' . $TrfLine->ReceiveQty  . ' @ ' . number_format($EstimatedAvgCost,2) . "',
					" . -$EstimatedAvgCost * $TrfLine->ReceiveQty. ",
					" . $unidadCompra . ")";
				//echo "<br>7.-" . $SQL;
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de reverso no se realizo');
		$DbgMsg = _('El SQL utilizado es');
		$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);
	
			
		$TextoOrden=" DE LA ORDEN DE COMPRA: ".$OrderNoCompra;
		$isql = "insert into stockmoves(stkmoveno,stockid,type,transno,loccode,trandate,debtorno,
			branchcode,price,prd,reference,qty,discountpercent,standardcost,show_on_inv_crds,newqoh,
			hidemovt,narrative,warranty,tagref,discountpercent1,discountpercent2,totaldescuento,avgcost,standardcostv2)
			values(NULL,'" . $TrfLine->StockID . "','20','" . $InvoiceNoCompra . "','" . $almacenCompra  . "',Now(),'','','". $costofactproveedoruno . "','" . $PeriodNo . "',
			'FACTURA DE COMPRA ".$InvoiceNoCompra." CON RECEPCION:".$GRN.$TextoOrden ."','0','0','" . $costofactproveedoruno . "','1','0','0','FACTURA DE COMPRA ".$InvoiceNo." CON RECEPCION:".$GRN.$TextoOrden.' '. $SerieNo ."','0','" . $unidadCompra . "','0','0','0','0','0')";
		$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockmoves debido a ');
		$DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
		$Result = DB_query($isql,$db,$ErrMsg,$DbgMsg,true);
		// inserta en la cuenta contable el movimiento
		$SQL = 'INSERT INTO gltrans (type, 
						typeno, 
						trandate, 
						periodno, 
						account, 
						narrative, 
						amount,
						tag) 
				VALUES (20, ' .
				 $InvoiceNoCompra . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ', ' . $GLCode .
				 ", '" . $ProveedorCompra . ' - ' . _('GRN') . ' ' . $GRN . ' - ' .
				 $TrfLine->StockID . ' x ' . $TrfLine->ReceiveQty . ' @ ' .
				$CurrAbrev . ' ' . $costofactproveedoruno . ' @ ' . _('a rate of') . ' ' .
				 $Rate .' '. $EnteredGRN->SerieNo. "', " .
				 round(($costofactproveedoruno * $TrfLine->ReceiveQty)  / $Rate,2) . ',
				 '.$unidadCompra.')';
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');
		$DbgMsg = _('The following SQL to insert the GL transaction was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		
		/*****************************************************************************************************/
		//inserta el detalle de la venta
		/*****************************************************************************************************/
		$LineItemsSQL = $StartOf_LineItemsSQL .
		$indice . ',' . $OrderNo . ','."'" . $TrfLine->StockID. "'".','. $EstimatedAvgCost . ',' . $TrfLine->ReceiveQty  . ',
		0,0,0,"Venta x transferencia",0,now(),'."'" . $almacenVenta . "'".',0,0,0,'. $TrfLine->ReceiveQty.')';
		$ErrMsg = _('No se puede agregar el producto a la orden por que');
		$Result = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);
		
		$SQL = "UPDATE salesorderdetails
			SET qtyinvoiced = qtyinvoiced + " . $TrfLine->ReceiveQty. ",
			    actualdispatchdate = '" . Date('Y-m-d') .  "',
			    completed= 1
			WHERE orderno = " .  $OrderNo . "
			AND orderlineno = '" . $indice. "'";
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
		$SQL = "INSERT INTO stockmoves (
			stockid,
			type,
			transno,
			loccode,
			trandate,
			debtorno,
			branchcode,
			price,
			prd,
			reference,
			qty,
			discountpercent,
			discountpercent1,
			discountpercent2,
			totaldescuento,
			standardcost,
			newqoh,
			warranty,
			narrative,
			tagref,
			avgcost
			)
		VALUES ('" . $TrfLine->StockID . "',
			" . $tipodefacturacion . ",
			" . $InvoiceNo . ",
			'" .$almacenVenta . "',
			'" . Date('Y-m-d') . "',
			'" . $DebtorNo . "',
			'" . $DebtorNo . "',
			" .  $costofactproveedoruno . ",
			" .  $PeriodNo . ",
			'" . $OrderNo . "',
			" . -$TrfLine->ReceiveQty . ",
			0,
			0,
			0,
			0,
			" . $costofactproveedoruno . ",
			0,
			0,
			'" . DB_escape_string($Comments) . "',
			" . $unidadVenta . ",
			'" . $costofactproveedoruno . "'
			)";
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
		$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
		
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
			" . $tipodefacturacion . ",
			" . $InvoiceNo . ",
			'" . Date('Y-m-d') . "',
			" . $PeriodNo . ",
			" . GetCOGSGLAccount($Area, $TrfLine->StockID, $DefaultSalesType, $db) . ",
			'" . $DebtorNo . " - " . $TrfLine->StockID . " | ". $_POST['ItemDescription']." x " . $TrfLine->ReceiveQty . " @ " . $costofactproveedoruno . " |".$seriesventa. "',
			" . $costofactproveedoruno* $TrfLine->ReceiveQty . ",
			" . $unidadVenta . "
		)";

		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
		$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$StockGLCode = GetStockGLCode($TrfLine->StockID,$db);
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
				" . $tipodefacturacion . ",
				" . $InvoiceNo . ",
				'" . $DefaultDispatchDate . "',
				" . $PeriodNo . ",
				" . $StockGLCode['stockact'] . ",
				'" . $DebtorNo . " - " . $TrfLine->StockID . " | ".$_POST['ItemDescription'] . " x " . $TrfLine->ReceiveQty . " @ " .$costofactproveedoruno. " |".$seriesventa. "',
				" . (-$costofactproveedoruno * $TrfLine->ReceiveQty) . ",
				" . $unidadVenta . "
			)";
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
		$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
		$SalesGLAccounts = GetSalesGLAccount($Area, $TrfLine->StockID, $DefaultSalesType, $db);
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
				" . $tipodefacturacion . ",
				" . $InvoiceNo . ",
				'" . Date('Y-m-d') . "',
				" . $PeriodNo . ",
				" . $SalesGLAccounts['salesglcode'] . ",
				'" . $DebtorNo . " - " . $TrfLine->StockID . " | ".$_POST['ItemDescription'] . " x " . $TrfLine->ReceiveQty . " @ " . $costofactproveedoruno . " @ " . (1/$Rate) . " |". $seriesventa."',
				" . (-$costofactproveedoruno * $TrfLine->ReceiveQty/$Rate) . ",
				" . $unidadVenta . "
			)";
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
		$DbgMsg = '<br>' ._('El siguiente SQL para insertar en GLTrans es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
		
		
		
		$totalmovimiento=$totalmovimiento+($costofactproveedoruno*$TrfLine->ReceiveQty);
	}// fin de cant >0
	$indice=$indice+1;
	
	
}
$TaxTotal=0;
$accountxtype=SupplierAccount($typesupplier,'gl_accountsreceivable',$db);
$CreditorsAct = $accountxtype;	
$SuppReference='Factura Automatica '.$InvoiceNoCompra;
// movimientos de CXP para la compra
$SQL = 'INSERT INTO gltrans (type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				tag)
		VALUES (20, ' .
		 $InvoiceNoCompra . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ', ' . $CreditorsAct.
		 ", '" . $ProveedorCompra . ' - ' . _('Inv') . ' ' .
		 $SuppReference . ' ' . $CurrAbrev .
		 number_format( $totalmovimiento + $TaxTotal,2)  .
		 ' @ ' . _('a rate of') . ' ' . $Rate . "', " .
		 -round(($totalmovimiento + ( $TaxTotal / $Rate)),2) . ',
		 '.$unidadCompra.')';
$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the control total could not be added because');
$DbgMsg = _('The following SQL to insert the GL transaction was used');
$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
//ingresa el movimiento en las cuentas por pagar del proveedor, deja pendiente el pago
$SQL = 'INSERT INTO supptrans (transno,
			tagref,
			type, 
			supplierno, 
			suppreference,
			origtrandate,
			trandate, 
			duedate, 
			ovamount, 
			ovgst, 
			rate, 
			transtext)
VALUES ('. $InvoiceNoCompra . ",
	'" . $unidadCompra. "', 
	20 , 
	'" . $ProveedorCompra . "',
	'" . $SuppReference. "',
	now(),				
	'" . Date('Y-m-d') . "', 
	'" . Date('Y-m-d') . "', 
	" . round($totalmovimiento,2) . ', 
	' . round($TaxTotal,2) . ', 
	' .  $Rate . ", 
	'" . $Comments . "')";
//echo $SQL;
$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier invoice transaction could not be added to the database because');
$DbgMsg = _('The following SQL to insert the supplier invoice was used');
$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
$SuppTransID = DB_Last_Insert_ID($db,'supptrans','id');			
// documento de cuantas por cobrar
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
			" . $tipodefacturacion . ",
			" . $InvoiceNo . ",
			'" . ('Y-m-d') . "',
			" . $PeriodNo . ",
			" .  $cuentacargo . ",
			'" . $DebtorNo . " @".$_SESSION['Items'.$identifier]->CustomerName."',
			" . $totalmovimiento . ",
			" .$unidadVenta . "
		)";
$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La cuenta por cobrar no se ha insertado, por que');
$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


$fechaemision=date('Y-m-d H:i:s');
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
		currcode,
		tagref,
		folio,
		origtrandate,
		discountpercentpayment,
		taxret
		)
	VALUES (
		". $InvoiceNo . ",
		" . $tipodefacturacion . ",
		'" . $DebtorNo . "',
		'" . $DebtorNo . "',
		'" . Date('Y-m-d') . "',
		" . $PeriodNo . ",
		'',
		'" . $DefaultSalesType . "',
		" .  $OrderNo . ",
		" . $totalmovimiento . ",
		" . $TaxTotal . ",
		0,
		" . $Rate . ",
		'" . str_replace('CANCELADA','',DB_escape_string(strtoupper($Comments))) . "',
		0,
		'"  . $OrderNo  . "',
		'" . $CurrAbrev . "',
		" . $unidadVenta .",
		'" . $folio . "',
		'" . $fechaemision . "',
		0,
		'-" . $TaxTotal . "'
	)";

$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la factura para el cliente no se realizo');
$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
//echo '<br>'.$SQL;
$DebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');

$SQL=" SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice
		       FROM legalbusinessunit l, tags t
		       WHERE l.legalid=t.legalid AND tagref='".$unidadVenta."'";
$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
if (DB_num_rows($Result)==1) {
	$myrowtags = DB_fetch_array($Result);
	$rfc=trim($myrowtags['taxid']);
	$keyfact=$myrowtags['address5'];
	$nombre=$myrowtags['tagname'];
	$area=$myrowtags['areacode'];
	$legaid=$myrowtags['legalid'];
	$tipofacturacionxtag=$myrowtags['typeinvoice'];
}
//****//
$InvoiceNoTAG = DocumentNext(10, $unidadVenta,$area,$legaid, $db);
$separa = explode('|',$InvoiceNoTAG);
$serie = $separa[1];
$folio = $separa[0];

$factelectronica= XSAInvoicing($InvoiceNo, $OrderNo, $DebtorNo, $tipodefacturacion,$unidadVenta,$serie,$folio, $db);
// Envia  los datos al archivooooo
$codigoref=strtoupper($_SESSION['Items'.$identifier]->CustRef);
$tipoarea=add_cerosstring($area,2);
$translegal=add_cerosstring($legaid,2);
$cuentareferenciada=$translegal.$tipoarea.$codigoref;
//extrae banco activo para cuentas referenciadas
//Actualizar el documento para folio
$SQL="UPDATE debtortrans
      SET folio='" . $serie.'|'.$folio . "',
	  ref1='" . $cuentaref. "',
	  showvehicle=0,
	  showcomments=0
      WHERE transno=".$InvoiceNo." and type=".$tipodefacturacion;
$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
// Consulta el total de productos vendidos
//		$Result = DB_Txn_Commit($db);		

$param=array('in0'=>$empresa, 'in1'=>$nombre,'in2'=>$tipo,'in3'=>$myfile,'in4'=>$factelectronica);
if ($tipofacturacionxtag==1){
	try{	
		$client = new SoapClient($_SESSION['XSA']."xsamanager/services/FileReceiverService?wsdl");
		$codigo=$client->guardarDocumento($param);
	}catch (SoapFault $exception) {
		$errorMessage = $exception->getMessage();
	}
	$liga=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=".$serie."&folio=".$folio."&tipo=PDF&rfc=".$rfc."&key=".$keyfact;
	$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' .$liga . '">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
} elseif($tipofacturacionxtag==2){
	//$XMLElectronico=generaXML($factelectronica,'ingreso',$unidadVenta,$serie,$folio,$DebtorTransID,'Facturas',$OrderNo,$db);
	$liga="PDFInvoice.php?&clave=chequepoliza_sefia";
	$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PDFInvoice.php?OrderNo='.$OrderNo.'&TransNo=' . $InvoiceNo .'&Type='.$tipodefacturacion.'&Tagref='.$_SESSION['Tagref'].'">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
} elseif($tipofacturacionxtag==3){
	//$XMLElectronico=generaXML($factelectronica,'ingreso',$_SESSION['Tagref'],$serie,$folio,$DebtorTransID,'Facturas',$OrderNo,$db);
	$liga="PDFInvoiceTemplate.php?clave=x";
	$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PDFInvoiceTemplate.php?OrderNo='.$OrderNo.'&TransNo=' . $InvoiceNo .'&Type='.$tipodefacturacion.'&Tagref='.$_SESSION['Tagref'].'">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
}else{
		$liga = GetUrlToPrintNu($unidadVenta,$area,$legaid,10,$db);
		$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'. $liga . SID .'&identifier='.$identifier . '&OrderNo='.$InvoiceNo.'&TransNo=' . $OrderNo .'&verplaca='.$verplaca .'&verkilometraje='.$verkm .'&verserie='.$verserie .'&vercomentarios='.$vercomentarios.'&Tagref='.$_SESSION['Tagref'].'">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
}
echo $liga;
?>