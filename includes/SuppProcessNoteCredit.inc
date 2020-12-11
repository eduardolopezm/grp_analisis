<?php
//*********************************ALTA DE LA NOTA DE CREDITO **********************************************
$_POST['CreditType']="Return";

$SQLCurrency="SELECT c.rate
	       FROM currencies c
	       WHERE c.currabrev='".$_SESSION['CurrAbrev']."'";
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

$fechaemision=$_POST['fechafactura'];
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
                " . $_SESSION['Tagref'] .",
                " . $tiponota . ",
                '" . $_SESSION['Items'.$identifier]->SupplierNo . "',
                '" . $DefaultDispatchDate . "',
                '" . $DefaultDispatchDate . "',
                " . -($_SESSION['Items'.$identifier]->total) . ",
                " . -$TaxTotal . ",
                " . $OrderNo . ",
                " . $_SESSION['CurrencyRate'] . ",
                '" . $_POST['Comments'] .' '.$PeriodNo . 'Trans-' . $OrderNo . "',
                '"  . $_SESSION['CurrAbrev']  . "',
		'"  . $fechaemision  . "'
		

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
// Actualiza la orden de nota de credito a 2

foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
    if ($OrderLine->Quantity >0){
            $LocalCurrencyPrice= round(($OrderLine->Price / $_SESSION['CurrencyRate']),2);
            /*Determine the type of stock item being credited */
            $SQL = "SELECT mbflag FROM stockmaster WHERE stockid = '" . $OrderLine->StockID . "'";
            $Result = DB_query($SQL,$db, _('No se puede determinar la bandera del stockmaster ') . ' ' . $OrderLine->StockID . ' ' . _('es producto terminado o manufacturado'),_('El Sql que fallo es:'),true);
            $MBFlagRow = DB_fetch_row($Result);
            $MBFlag = $MBFlagRow[0];
            if ($MBFlag=='M' OR $MBFlag=='B'){
                    /*Need to get the current location quantity will need it later for the stock movements */
                    $SQL="SELECT locstock.quantity
                            FROM locstock
                            WHERE locstock.stockid='" . $OrderLine->StockID . "'
                            AND loccode= '" . $OrderLine->AlmacenStock . "'";
                    $Result = DB_query($SQL, $db);
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

            if ($_POST['CreditType']=='Return'){
            
                  $SQL = "UPDATE suppnotesorderdetails
			  SET qtyinvoiced = qtyinvoiced + " . $OrderLine->Quantity . ",
                              actualdispatchdate = '" . $DefaultDispatchDate .  "'
			  WHERE orderno = " .  $OrderNo . "
                                AND orderlineno = '" . $OrderLine->LineNumber . "'";
                    $ErrMsg =  _('ERROR CRITICO') . '! ' . _('TOME NOTA DEL ERROR') . ': ' . _('No se puede regresar el inventario');
                    $DbgMsg = _('El SQL utilizado es');
                    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                    /* Update location stock records if not a dummy stock item */
                    if ($MBFlag=="B" OR $MBFlag=="M") {
                            $SQL = "UPDATE locstock
                                    SET locstock.quantity = locstock.quantity - " . $OrderLine->Quantity . "
                                    WHERE locstock.stockid = '" . $OrderLine->StockID . "'
                                    AND loccode = '" . $OrderLine->AlmacenStock . "'";
                            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El stock por almacen no se puede modificar');
			    $DbgMsg = _('El SQL para actualizar el registro de las existencias del almacen es:');
                            $Result = DB_query($SQL, $db, $ErrMsg,$DbgMsg,true);
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
                            AND bom.parent='" . $OrderLine->StockID . "'
                            AND bom.effectiveto > '" . Date('Y-m-d') . "'
                            AND bom.effectiveafter < '" . Date('Y-m-d') . "'";
                            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo recuperar los componentes de montaje de la base de datos de'). ' '. $OrderLine->StockID . _('por que').' ';
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
                                                    AND loccode= '" . $OrderLine->AlmacenStock . "'";
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
                                                            tagref)
                                            VALUES ('" . $AssParts['component'] . "',
                                                    " . $tiponota . ",
                                                    " . $CreditNo . ",
                                                    '" . $OrderLine->AlmacenStock . "',
                                                    '" . $DefaultDispatchDate . "',
                                                    " . $PeriodNo . ",
                                                    '" . _('Nota Credito Proveedor').' : '.$_SESSION['Items'.$identifier]->SupplierNo . ' : ' . $OrderNo . ' ' . _('armado') . ': ' . $OrderLine->StockID . "',
                                                    " . -($AssParts['quantity'] * $OrderLine->Quantity) . ",
                                                    " . $AssParts['standard'] . ",
                                                    0,
                                                    " . ($QtyOnHandPrior - ($AssParts['quantity'] * $OrderLine->Quantity)) . "
                                                    ," . $_SESSION['Tagref'] . ")";
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
                                                            tagref)
                                                    VALUES ('" . $AssParts['component'] . "',
                                                    " . $tiponota . ",
                                                    " . $CreditNo . ",
                                                    '" . $OrderLine->AlmacenStock . "',
                                                    '" . $DefaultDispatchDate . "',
                                                    " . $PeriodNo . ",
                                                    '" . _('Nota Credito Proveedor').' : '.$_SESSION['Items'.$identifier]->SupplierNo . ' : ' . $OrderNo . ' ' . _('armado') . ': ' . $OrderLine->StockID . "',
                                                    " . -($AssParts['quantity'] * $OrderLine->Quantity) . ",
                                                    " . $AssParts['standard'] . ",
                                                    0," . $_SESSION['Tagref'] . ")";
                                    }
                                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de existencias de los componentes de ensamble de'). ' '. $OrderLine->StockID . ' ' . _('no se pudieron registrar por que');
        			    $DbgMsg = _('El SQL para insertar componentes y montaje de los registros de movimientos de existencias es:');
                                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                    if ($Component_MBFlag=="M" OR $Component_MBFlag=="B"){
                                            $SQL = "UPDATE locstock
                                                    SET locstock.quantity = locstock.quantity - " . $AssParts['quantity'] * $OrderLine->QtyDispatched . "
                                                    WHERE locstock.stockid = '" . $AssParts['component'] . "'
                                                    AND loccode = '" . $OrderLine->AlmacenStock . "'";
                                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registro de las existencias del almacen no se puede actualizar para un componente de ensamble, por que');
				    $DbgMsg = _('El siguiente SQL para actualizar el registro de las existencias por almacen para el componente de ensamble es:');
                                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                    }
                            } /* end of assembly explosion and updates */
                            /*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
                            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->StandardCost = $StandardCost;
                            $OrderLine->StandardCost = $StandardCost;
                    }
                    // Suma descuentos en cascada en el campo de descuento para los movimientos de stock
                    $montodescuento = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
                    //DESCUENTO DOS
                    $montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent1);
                    //DESCUENTO TRES
                    $montodescuento=$montodescuento * (1 - $OrderLine->DiscountPercent2);
                    $montodescuento=($OrderLine->Quantity * $OrderLine->Price)-$montodescuento;
                    
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
                                            tagref)
                                    VALUES ('" . $OrderLine->StockID . "',
                                            " . $tiponota . ",
                                            " . $CreditNo . ",
                                            '" . $OrderLine->AlmacenStock . "',
                                            '" . $DefaultDispatchDate . "',
                                            " . $LocalCurrencyPrice . ",
                                            " . $PeriodNo . ",
                                            '" . _('Nota Credito Proveedor').' : '.$_SESSION['Items'.$identifier]->SupplierNo . ' Order: ' . $OrderNo . "',
                                            " . -$OrderLine->Quantity . ",
                                            " . $OrderLine->DiscountPercent . ",
                                            " . $OrderLine->DiscountPercent1 . ",
                                            " . $OrderLine->DiscountPercent2 . ",
                                            " . $montodescuento . ", 
                                            " . $OrderLine->StandardCost . ",
                                            " .  ($QtyOnHandPrior - $OrderLine->Quantity) . ",
                                            '" . $OrderLine->Narrative . "'," . $_SESSION['Tagref'] . ")";
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
                                    tagref)
                            VALUES ('" . $OrderLine->StockID . "',
                                    " . $tiponota . ",
                                    " . $CreditNo . ",
                                    '" . $OrderLine->AlmacenStock . "',
                                    '" . $DefaultDispatchDate . "',
                                    " . $LocalCurrencyPrice . ",
                                    " . $PeriodNo . ",
                                    '" . _('Nota Credito Proveedor').' : '.$_SESSION['Items'.$identifier]->SupplierNo . ' Order: ' . $OrderNo . "',
                                    " . -$OrderLine->Quantity . ",
                                    " . $OrderLine->DiscountPercent . ",
                                    " . $OrderLine->DiscountPercent1 . ",
                                    " . $OrderLine->DiscountPercent2 . ",
                                    " . $montodescuento . ", 
                                    " . $OrderLine->StandardCost . ",
                                    '" . $OrderLine->Narrative . "',
                                    " . $_SESSION['Tagref'] . "
                            )";
                    }

                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de stock no puede insertarse, por que');
		    $DbgMsg = _('El siguiente SQL para insertar la contabilidad deL movimiento existencias utilizado es:');
		    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                    $StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
                    
                    /*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/
                    //echo "<div align=left><pre>"; var_dump($OrderLine); echo "</pre> </div>";
                    if ($OrderLine->Controlled ==1){
                            foreach($OrderLine->SerialItems as $Item){
                                    /*We need to add the StockSerialItem record and The StockSerialMoves as well */
                                    $SQL = "SELECT quantity from stockserialitems
                                            WHERE stockid='" . $OrderLine->StockID . "'
                                            AND loccode='" . $OrderLine->AlmacenStock . "'
                                            AND serialno='" . $Item->BundleRef . "'";
                                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be selected because');
                                    $DbgMsg = _('The following SQL to select the serial stock item record was used');
                                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                    if (DB_num_rows($Result)==0){
                                            $SQL = "INSERT INTO stockserialitems (stockid, loccode, serialno, quantity)
                                                    VALUES
                                                    ('" . $OrderLine->StockID . "', '" . $OrderLine->AlmacenStock . "', '" . $Item->BundleRef . "',  ". $Item->BundleQty .")";

                                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
                                            $DbgMsg = _('The following SQL to update the serial stock item record was used');
                                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                    } else {

                                            $SQL = "UPDATE stockserialitems
                                                    SET quantity= quantity - " . $Item->BundleQty . "
                                                    WHERE stockid='" . $OrderLine->StockID . "'
                                                    AND loccode='" . $OrderLine->AlmacenStock . "'
                                                    AND serialno='" . $Item->BundleRef . "'";
                                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
                                            $DbgMsg = _('The following SQL to update the serial stock item record was used');
                                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                    }

                                    /* now insert the serial stock movement */
                                    $StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
                                    $SQL = "INSERT INTO stockserialmoves (stockmoveno, stockid, serialno, moveqty) VALUES (" . $StkMoveNo . ", '" . $OrderLine->StockID . "', '" . $Item->BundleRef . "', " . -$Item->BundleQty . ")";
                                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('El movimiento del numero de serie no se pudo recuperar');
                                    $DbgMsg = _('El SQL utilizado es');
                                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                            }/* foreach controlled item in the serialitems array */
                    } /*end if the orderline is a controlled item */

            } 
            /*Get the ID of the StockMove... */
          //  $StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
            /*Insert the taxes that applied to this line */
            foreach ($OrderLine->Taxes as $Tax) {
		    $SQL="Select count(*) as cuenta from stockmovestaxes where taxauthid='". $Tax->TaxAuthID."'
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
					' . $Tax->TaxAuthID . ',
					' . $Tax->TaxRate . ',
					' . $Tax->TaxCalculationOrder . ',
					' . $Tax->TaxOnTax . ')';
		       // echo '<br>'.$SQL;
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Los impuestos y tasas aplicables a esta partida de la factura, no pueden insertarse, por que');
			$DbgMsg = _('El siguiente SQL para insertar los registros de detalle de valores del impuesto es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		    }
            }
    /* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/
            if ($_SESSION['CompanyRecord']['gllink_stock']==1 
                    
                    ){
		
		    $totalsindescuento=($OrderLine->Price * $OrderLine->Quantity)/$_SESSION['CurrencyRate'];
                    $montodescuento= (($OrderLine->Quantity * $OrderLine->Price)/$_SESSION['CurrencyRate']) * (1 - $OrderLine->DiscountPercent);
                    $montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent1);
                    $montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent2);
                   // $montodescuento=$totalsindescuento-$montodescuento;
                            $StockGLCode = GetStockGLCode($OrderLine->StockID, $db);
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
                                            '" . $_SESSION['Items'.$identifier]->SupplierNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->Price . "',
                                            " . -round(($montodescuento),2) . ",
                                            " . $_SESSION['Tagref'] . ")";
                    }
                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
		    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
                    $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

            } /* end of if GL and stock integrated and standard cost !=0 */
            if ($_SESSION['CompanyRecord']['gllink_creditors']==1 AND $OrderLine->Price !=0){
            //Post sales transaction to GL credit sales
                    //$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
                    /*$StockGLCode = GetStockGLCode($OrderLine->StockID,$db);
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
                                    " . $StockGLCode['purchpricevaract'] . ",
                                    '" . $_SESSION['Items'.$identifier]->SupplierNo . " ventas - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->Price . "',
                                    " . round(($OrderLine->Price * $OrderLine->Quantity)/$_SESSION['CurrencyRate'],2) . "
                                    ," . $_SESSION['Tagref'] . ")";

                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
		    $DbgMsg = '<br>' ._('El siguiente SQL para insertar en GLTrans es:');
                    $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
		   */
                    // extrae el total de descuentos en cascada
                    $totalsindescuento=($OrderLine->Price * $OrderLine->Quantity)/$_SESSION['CurrencyRate'];
                    $montodescuento= (($OrderLine->Quantity * $OrderLine->Price)/$_SESSION['CurrencyRate']) * (1 - $OrderLine->DiscountPercent);
                    $montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent1);
                    $montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent2);
                    $montodescuento=$totalsindescuento-$montodescuento;
                    
                    // concatena el narrative que justifica contablemente el descuento en casdada
                    $textodescuento= $_SESSION['Items'.$identifier]->SupplierNo . " - " . $OrderLine->StockID;		
                    $textodescuento= $textodescuento . " @ " . ($OrderLine->DiscountPercent * 100). " % ";
                    $textodescuento= $textodescuento . " @ " . ($OrderLine->DiscountPercent1 * 100)." % ";
                    $textodescuento= $textodescuento . " @ " . ($OrderLine->DiscountPercent2 * 100)." % ";
				
		    if ($montodescuento!=0 ){

                    //if ($OrderLine->DiscountPercent !=0){
		    $StockGLCode = GetStockGLCode($OrderLine->StockID, $db);
                         /*  $SQL = "INSERT INTO gltrans (type,
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
                                            " . $StockGLCode['stockact'] . ",
                                            '" . $textodescuento."',
                                            " . -round($montodescuento,2) . "
                                            ," . $_SESSION['Tagref'] . ")";
                            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El descuento de la venta no se ha insertado, por que');
			    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
                            $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
			*/
                    } /*end of if discount !=0 */
            } /*end of if sales integrated with debtors */
    } /*Quantity dispatched is more than 0 */


if ($_SESSION['CompanyRecord']['gllink_creditors']==1){
/*Post credit note transaction to GL credit debtors, debit freight re-charged and debit sales */
    if (($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost + $TaxTotal) !=0) {
	
	    //OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
            $tipoproveedor = ExtractTypeSupplier($_SESSION['Items'.$identifier]->SupplierNo,$db);
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
                                    '" . $_SESSION['Items'.$identifier]->SupplierNo . "',
                                    " . round(($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost + $TaxTotal)/$_SESSION['CurrencyRate'],2) . "
                            ," . $_SESSION['Tagref'] . ")";
           $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La cuenta por cobrar no se ha insertado, por que');
	    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
            $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
    }

/*Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery */


    if (round($_SESSION['Items'.$identifier]->FreightCost,2) !=0 ) {
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
                            " . $_SESSION['CompanyRecord']['freightact'] . ",
                            '" . $_SESSION['Items'.$identifier]->SupplierNo . "',
                            " . -round($_SESSION['Items'.$identifier]->FreightCost/$_SESSION['CurrencyRate'],2) . "
                            ," . $_SESSION['Tagref'] . ")";

            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The freight GL posting for this credit note could not be inserted because');
            $DbgMsg = _('The following SQL to insert the GLTrans record was used');
            $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
    }

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
                                    '" . $_SESSION['Items'.$identifier]->SupplierNo . "', 
                                    " . -($TaxAmount/$_SESSION['CurrencyRate']) . ",
                                    " . $_SESSION['Tagref'] . ")";
                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El impuesto de la venta no se ha insertado, por que');
		    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
                    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            }
    }
}
//*********************************FIN DE DETALLE DE LA NOTA DE CREDITO ******************************************************

?>




