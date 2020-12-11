<?

	function RegistrarNCRDevolucion($arrTransfer,$OrderNo,$total,$TaxTotal,$TaxTotals,$TaxGLCodes,$totXline,$tiponota,$currcode,$supplierno,$tagref,$db){

		$CreditNo = GetNextTransNo($tiponota, $db);
		$DefaultDispatchDate = date("Y-m-d");
		$PeriodNo = GetPeriod($DefaultDispatchDate, $db);
	
	
		$SQLCurrency="SELECT c.rate
				   FROM currencies c
				   WHERE c.currabrev='".$currcode."'";
		$ErrMsg =  _('No se pudo recuperar el valor de la moneda ');
		$GetCurrency = DB_query($SQLCurrency,$db,$ErrMsg);
		if (DB_num_rows($GetCurrency)==1) {	       
			$myrowCurrency = DB_fetch_array($GetCurrency);
			$_SESSION['CurrencyRate']=$myrowCurrency['rate'];
		}else{
			$_SESSION['CurrencyRate']=1;
		}
		
		// actualizar la order
		$SQL = 'UPDATE suppnotesorders
				SET    quotation = 2
				WHERE suppnotesorders.orderno=' . $OrderNo;
		$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La actualizacion de la orden de nota de credito');
		$DbgMsg = _('El SQL utilizado para la actualizacion de la nota de credito es');
		$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);              
		
		$fechaemision=date;
		$separae = explode('/',$fechaemision);
		$diae=$separae[0];;
		$mese = $separae[1];
		$anioe = $separae[2];
		$horax = date('H:i:s');
		$horax = strtotime($horax);
		$hora=date(H)-1;
		$minuto=date('i');
		$segundo=date('s');
		$fechainic=mktime($hora,$minuto,$segundo,rtrim($mese),rtrim($diae),rtrim($anioe));
		$fechaemision=date("Y-m-d H:i:s",$fechainic);
			
		$SQL = "INSERT INTO supptrans (transno,
										  tagref,
										  type,
										  supplierno,
										  trandate,
										  duedate,
										  ovamount,
										  ovgst,
										  order_,
										  rate,
										  transtext,
										  currcode,
										  origtrandate
						  
										  
										  )
				VALUES (". $CreditNo . ",
						" . $tagref .",
						" . $tiponota . ",
						'" . $supplierno . "',
						'" . $DefaultDispatchDate . "',
						'" . $DefaultDispatchDate . "',
						" . -($total) . ",
						" . -$TaxTotal . ",
						" . $OrderNo . ",
						" . $_SESSION['CurrencyRate'] . ",
						'Periodo: ".$PeriodNo." Trans- ".$OrderNo."',
						'".$currcode."',
						'".$fechaemision."'
						)";
	
		$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la nota de credito para el proveedor no se realizo');
		$DbgMsg = _('El SQL utilizado para el registro de la nota de credito es:');
		$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
		$CreditTransID = DB_Last_Insert_ID($db,'supptrans','id');
		foreach ($TaxTotals AS $TaxAuthID => $TaxAmount) {
			$SQL = 'INSERT INTO supptranstaxes (supptransid,
													taxauthid,
													taxamount)
							VALUES (' . $CreditTransID . ',
									' . $TaxAuthID . ',
									' . -($TaxAmount/$_SESSION['CurrencyRate']) . ')';
			
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('impuesto de credito no insertado');
			$DbgMsg = _('El SQL utilizado fue:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
		//*********************************AQUI ESTA LA PARTE DETALLE DE LA NOTA DE CREDITO **********************************************
		$line=0;
		foreach ($arrTransfer->TransferItem as $TrfLine) {

			  $LocalCurrencyPrice= round(($totXline[$line]['total'] / $_SESSION['CurrencyRate']),2);
			  $desc1 = $totXline[$line]['desc1'];
			  $desc2 = $totXline[$line]['desc2'];
			  $desc3 = $totXline[$line]['desc3'];
			  
			  /*Determine the type of stock item being credited */
			  $SQL = "SELECT mbflag FROM stockmaster WHERE stockid = '" . $TrfLine->StockID . "'";
			  $Result = DB_query($SQL,$db, _('No se puede determinar la bandera del stockmaster ') . ' ' . $TrfLine->StockID . ' ' . _('es producto terminado o manufacturado'),_('El Sql que fallo es:'),true);
			  $MBFlagRow = DB_fetch_row($Result);
			  $MBFlag = $MBFlagRow[0];
			  if ($MBFlag=='M' OR $MBFlag=='B'){
					  /*Need to get the current location quantity will need it later for the stock movements */
					  $SQL="SELECT locstock.quantity
							  FROM locstock
							  WHERE locstock.stockid='" . $TrfLine->StockID . "'
							  AND loccode= '" .$arrTransfer->StockLocationFrom. "'";
					  $Result = DB_query($SQL, $db);
					  if (DB_num_rows($Result)==1){
							  $LocQtyRow = DB_fetch_row($Result);
							  $QtyOnHandPrior = $LocQtyRow[0];
					  } 
					  else {
							  /*There must actually be some error this should never happen */
							  $QtyOnHandPrior = 0;
					  }
			  } 
			  else {
					  $QtyOnHandPrior =0; //because its a dummy/assembly/kitset part
			  }
  
			  //if ($_POST['CreditType']=="Return"){
			  
			  /* Update location stock records if not a dummy stock item */
			  if ($MBFlag=="B" OR $MBFlag=="M") {
					  /*$SQL = "UPDATE locstock
							  SET locstock.quantity = locstock.quantity - " . $TrfLine->Quantity . "
							  WHERE locstock.stockid = '" . $TrfLine->StockID . "'
							  AND loccode = '" . $arrTransfer->StockLocationFrom . "'";
					  
					  $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El stock por almacen no se puede modificar');
					  $DbgMsg = _('El SQL para actualizar el registro de las existencias del almacen es:');
					  */
					  //$Result = DB_query($SQL, $db, $ErrMsg,$DbgMsg,true); comentado porque ya ese movimiento se hace anteriormente
			  } else if ($MBFlag=='A'){ /* its an assembly */
					  /*Need to get the BOM for this part and make stock moves for the components
					  and of course update the Location stock balances */

					  $StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
					  $sql = "SELECT
							  bom.component,
							  bom.quantity,
					  stockmaster.materialcost 
							  + stockmaster.labourcost 
							  + stockmaster.overheadcost AS standard
					  FROM bom, 
							  stockmaster
					  WHERE bom.component=stockmaster.stockid
					  AND bom.parent='" . $TrfLine->StockID . "'
					  AND bom.effectiveto > '" . date('Y-m-d') . "'
					  AND bom.effectiveafter < '" . date('Y-m-d') . "'";
					  $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo recuperar los componentes de montaje de la base de datos de'). ' '. $$TrfLine->StockID . _('por que').' ';
					  $DbgMsg = _('El SQL utilizado es:');
					  $AssResult = DB_query($sql,$db, $ErrMsg, $DbgMsg, true);
					  while ($AssParts = DB_fetch_array($AssResult,$db)){
			  
							  $StandardCost += $AssParts['standard'];
							  /*Determine the type of stock item being credited */
							  $SQL = "SELECT mbflag
									  FROM
									  stockmaster
									  WHERE stockid = '" . $AssParts['component'] . "'";
							  $Result = DB_query($SQL,$db);
							  $MBFlagRow = DB_fetch_row($Result);
							  $Component_MBFlag = $MBFlagRow[0];

							  /* Insert stock movements for the stock coming back in - with unit cost */
							  if ($Component_MBFlag=='M' OR $Component_MBFlag=='B'){
									  /*Need to get the current location quantity will need it later for the stock movement */
									  $SQL="SELECT locstock.quantity
											  FROM locstock
											  WHERE locstock.stockid='" . $AssParts['component'] . "'
											  AND loccode= '" . $arrTransfer->StockLocationFrom . "'";
									  $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se puede recuperar componentes y montaje de las cantidades ubicación de las existencias debido a ');
									  $DbgMsg = _('El SQL que fallo es:');        
									  $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
									  if (DB_num_rows($Result)==1){
											  $LocQtyRow = DB_fetch_row($Result);
											  $QtyOnHandPrior = $LocQtyRow[0];
									  } else {
											  /*There must actually be some error this should never happen */
											  $QtyOnHandPrior = 0;
									  }
							  } else {
									  $QtyOnHandPrior =0; //because its a dummy/assembly/kitset part
							  }

							  if ($Component_MBFlag=="M" OR $Component_MBFlag=="B"){
									  $SQL = "INSERT INTO stockmoves (
													  stockid,
													  type,
													  transno,
													  loccode,
													  trandate,
													  prd,
													  reference,
													  qty,
													  standardcost,
													  show_on_inv_crds,
													  newqoh,
													  tagref,
													  hidemovt)
									  VALUES ('" . $AssParts['component'] . "',
											  " . $tiponota . ",
											  " . $CreditNo . ",
											  '" . $arrTransfer->StockLocationFrom . "',
											  '" . $DefaultDispatchDate . "',
											  " . $PeriodNo . ",
											  '" . _('Nota Credito Proveedor').' : '.$supplierno . ' : ' . $OrderNo . ' ' . _('armado') . ': ' . $TrfLine->StockID . "',
											  " . -($AssParts['quantity'] * $TrfLine->Quantity) . ",
											  " . $AssParts['standard'] . ",
											  0,
											  " . ($QtyOnHandPrior - ($AssParts['quantity'] * $TrfLine->Quantity)) . "
											  ," . $tagref . ",
											  1)";
							  } else {

									  $SQL = "INSERT INTO stockmoves (
													  stockid,
													  type,
													  transno,
													  loccode,
													  trandate,
													  prd,
													  reference,
													  qty,
													  standardcost,
													  show_on_inv_crds,
													  tagref,
													  hidemovt)
											  VALUES ('" . $AssParts['component'] . "',
											  " . $tiponota . ",
											  " . $CreditNo . ",
											  '" . $arrTransfer->StockLocationFrom . "',
											  '" . $DefaultDispatchDate . "',
											  " . $PeriodNo . ",
											  '" . _('Nota Credito Proveedor').' : '.$supplierno . ' : ' . $OrderNo . ' ' . _('armado') . ': ' . $TrfLine->StockID . "',
											  " . -($AssParts['quantity'] * $TrfLine->Quantity) . ",
											  " . $AssParts['standard'] . ",
											  0," . $tagref . ",
											  1)";
							  }
							  $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de existencias de los componentes de ensamble de'). ' '. $TrfLine->StockID . ' ' . _('no se pudieron registrar por que');
				  $DbgMsg = _('El SQL para insertar componentes y montaje de los registros de movimientos de existencias es:');
							  $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					  } /* end of assembly explosion and updates */
					  /*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
					  /*$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->StandardCost = $StandardCost;
					  $OrderLine->StandardCost = $StandardCost;*/
			  }
			  // Suma descuentos en cascada en el campo de descuento para los movimientos de stock
			  $montodescuento = $TrfLine->Quantity * $price * (1 - $desc1);
			  //DESCUENTO DOS
			  $montodescuento=$montodescuento * (1 - $desc2);
			  //DESCUENTO TRES
			  $montodescuento=$montodescuento * (1 - $desc3);
			  $montodescuento=($TrfLine->Quantity * $price)-$montodescuento;
			  
			  /* Insert stock movements for the stock coming back in - with unit cost */
			  if ($MBFlag=="M" OR $MBFlag=="B"){
					  $SQL = "INSERT INTO stockmoves (
									  stockid,
									  type,
									  transno,
									  loccode,
									  trandate,
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
									  narrative,
									  tagref,
									  hidemovt)
							  VALUES ('" . $TrfLine->StockID . "',
									  " . $tiponota . ",
									  " . $CreditNo . ",
									  '" . $arrTransfer->StockLocationFrom . "',
									  '" . $DefaultDispatchDate . "',
									  " . $LocalCurrencyPrice . ",
									  " . $PeriodNo . ",
									  '" . _('Nota Credito Proveedor').' : '.$supplierno . ' Order: ' . $OrderNo . "',
									  " . -$TrfLine->Quantity . ",
									  " . $desc1 . ",
									  " . $desc2 . ",
									  " . $desc3 . ",
									  " . $montodescuento . ", 
									  '" . $StandardCost . "',
									  " .  ($QtyOnHandPrior - $TrfLine->Quantity) . ",
									  ''," . $tagref . ",
									  1)";
			  } else {

					  $SQL = "INSERT INTO stockmoves (
							  stockid,
							  type,
							  transno,
							  loccode,
							  trandate,
							  price,
							  prd,
							  reference,
							  qty,
							  discountpercent,
							  discountpercent1,
							  discountpercent2,
							  totaldescuento,
							  standardcost,
							  narrative,
							  tagref,
							  hidemovt)
					  VALUES ('" . $TrfLine->StockID . "',
							  " . $tiponota . ",
							  " . $CreditNo . ",
							  '" . $arrTransfer->StockLocationFrom . "',
							  '" . $DefaultDispatchDate . "',
							  " . $LocalCurrencyPrice . ",
							  " . $PeriodNo . ",
							  '" . _('Nota Credito Proveedor').' : '.$supplierno . ' Order: ' . $OrderNo . "',
							  " . -$TrfLine->Quantity . ",
							  " . $desc1 . ",
							  " . $desc2 . ",
							  " . $desc3 . ",
							  " . $montodescuento . ", 
							  " . $StandardCost . ",
							  '',
							  " . $tagref. ",
							  1
					  )";
			  }

			  $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de stock no puede insertarse, por que');
			  $DbgMsg = _('El siguiente SQL para insertar la contabilidad deL movimiento existencias utilizado es:');
			  $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			  $StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
			  
			  
			  if ($TrfLine->Controlled == 1){
					foreach($TrfLine->SerialItems as $Item){
						/* now insert the serial stock movement */
						$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
						$SQL = "INSERT INTO stockserialmoves (stockmoveno, stockid, serialno, moveqty) VALUES (" . $StkMoveNo . ", '" . $TrfLine->StockID . "', '" . $Item->BundleRef . "', " . -$Item->BundleQty . ")";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('El movimiento del numero de serie no se pudo recuperar');
						$DbgMsg = _('El SQL utilizado es');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					}/* foreach controlled item in the serialitems array */
			  } /*end if the orderline is a controlled item */
  
			  //} 
			  /*Get the ID of the StockMove... */
			 //  $StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
			  /*Insert the taxes that applied to this line */
			  
			  foreach ($totXline[$line]['taxs'] as $Tax) {
				  $SQL="Select count(*) as cuenta from stockmovestaxes where taxauthid='". $Tax['taxauthid']."'
					  and stkmoveno= ".$StkMoveNo;
				  $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Los impuestos y tasas aplicables a esta partida de la factura, no pueden insertarse, por que');
				  $DbgMsg = _('El siguiente SQL para insertar los registros de detalle de valores del impuesto es:');
				  $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				  if (DB_num_rows($Result)==0){
					  $SQL = 'INSERT INTO stockmovestaxes (stkmoveno,
									  taxauthid,
									  taxrate,
									  taxcalculationorder,
									  taxontax)
						  VALUES (' . $StkMoveNo . ',
							  ' . $Tax['taxauthid'] . ',
							  ' . $Tax['taxrate'] . ',
							  ' . $Tax['taxcalculationorder'] . ',
							  ' . $Tax['taxontax'] . ')';
						 // echo '<br>'.$SQL;
					  $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Los impuestos y tasas aplicables a esta partida de la factura, no pueden insertarse, por que');
					  $DbgMsg = _('El siguiente SQL para insertar los registros de detalle de valores del impuesto es:');
					  $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				  }
			  }
			  /* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/
			  if ($_SESSION['CompanyRecord']['gllink_stock']==1 ){
		  
					  $totalsindescuento=($totXline[$line]['total'])/$_SESSION['CurrencyRate'];
					  $montodescuento= (($totXline[$line]['total'])/$_SESSION['CurrencyRate']) * (1 - $desc1);
					  $montodescuento=$montodescuento * (1 -$$desc2);
					  $montodescuento=$montodescuento * (1 -$desc3);
					 // $montodescuento=$totalsindescuento-$montodescuento;
					  $StockGLCode = GetStockGLCode($TrfLine->StockID, $db);
					  $SQL = "INSERT INTO gltrans (type,
											  typeno,
											  trandate,
											  periodno,
											  account,
											  narrative,
											  amount, tag)
							  VALUES (". $tiponota  . ",
									  " . $CreditNo . ",
									  '" . $DefaultDispatchDate . "',
									  " . $PeriodNo . ",
									  " . $StockGLCode['stockact'] . ",
									  '" . $supplierno . " - " . $TrfLine->StockID . " x " . $TrfLine->Quantity . " @ " . $price . "',
									  " . -round(($montodescuento),2) . ",
									  " . $tagref . ")";

					  $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
					  $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
					  $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
			  }/* end of if GL and stock integrated and standard cost !=0 */
  
				  
		  $line++;
		  
	   }//for items NCR
	
	   if ($_SESSION['CompanyRecord']['gllink_creditors']==1){
			/*Post credit note transaction to GL credit debtors, debit freight re-charged and debit sales */
			if (($total + $TaxTotal) !=0) {
			
				//OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
				$tipoproveedor = ExtractTypeSupplier($supplierno,$db);
				$ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
				//$_SESSION['CompanyRecord']['creditorsact']
					$SQL = "INSERT INTO gltrans (type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount, tag)
									VALUES (" . $tiponota . ",
											" . $CreditNo . ",
											'" . $DefaultDispatchDate . "',
											" . $PeriodNo . ",
											" . $ctaxtipoproveedor . ",
											'" . $supplierno . "',
											" . round(($tottal + $TaxTotal)/$_SESSION['CurrencyRate'],2) . "
									," . $tagref . ")";
				   $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La cuenta por cobrar no se ha insertado, por que');
				$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
					$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
			}
	
			/*Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery */
	
	
			foreach ( $TaxTotals as $TaxAuthID => $TaxAmount){	
					if ($TaxAmount !=0 ){
				
							$SQL = "INSERT INTO gltrans (
											type, 
											typeno, 
											trandate, 
											periodno, 
											account, 
											narrative, 
											amount, tag
											) 
									VALUES (
											" . $tiponota . ", 
											" . $CreditNo . ", 
											'" . $DefaultDispatchDate . "', 
											" . $PeriodNo . ", 
											" . $TaxGLCodes[$TaxAuthID] . ", 
											'" . $supplierno . "', 
											" . -($TaxAmount/$_SESSION['CurrencyRate']) . ",
											" . $tagref. ")";
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El impuesto de la venta no se ha insertado, por que');
							$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
							$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					}
			}
	
		
		
		}
		
		return 1;

	}//function


?>