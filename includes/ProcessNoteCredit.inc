<?php
/* 
   ARCHIVO MODIFICADO POR: desarrollo
 FECHA DE MODIFICACION: 22-FEB-2011
 CAMBIOS:
   1. CAMBIO DE COSTO DE MOVIMIENTOS
 FIN DE CAMBIOS
 
   ARCHIVO MODIFICADO POR: desarrollo
 FECHA DE MODIFICACION: 25-MAR-2011
 CAMBIOS:
   1. ALTA DE CALCULO DE COSTO PROMEDIO
 FIN DE CAMBIOS
*/


//*********************************ALTA DE LA NOTA DE CREDITO **********************************************
$_POST['CreditType']="Return";

$_SESSION['CurrAbrev'] = $_SESSION['Items'.$identifier]->DefaultCurrency;

// actualizar la order
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
if($_SESSION['CountryOfOperation']!=$_SESSION['CurrAbrev']){
	  $fecharfact=$DefaultDispatchDate; // ,Date('Y').'-'.Date('m').'-'.Date('d');
	  $_SESSION['CurrencyRate']=GetCurrencyRateByDate($fecharfact, $_SESSION['CurrAbrev'],$db);
	  // en caso de que sea manual la aplicacion en moneda extranjera
	  if(isset($_POST['tcmanual']) && $_POST['tcmanual']>1 ){
	  	$_SESSION['CurrencyRate']=number_format(1/$_POST['tcmanual'],4);
	  }
	  
	  if($_SESSION['CurrencyRate']==0){
	    $SQL = "UPDATE notesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
	    $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la nota de credito no se pudo actualizar con el numero de factura');
	    $DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
	    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	    prnMsg(_('No se realizo la operacion por que no se ha capturado tipo de cambio para el dia en el que desea realizar la NC, se guardo como solicitud. Verifique con el administrador del sistema...'),'error');
	    $Result = DB_Txn_Commit($db);	
	    include('includes/footer.inc');
	    exit;
	  }
}
//valido que si es serailizado y el costo de la serie es 0 me la proporcione
$QuantityControlled=true;
foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
	if ($OrderLine->Controlled==1 OR $OrderLine->Serialised){
		if (count($OrderLine->SerialItems)==0){
			$QuantityControlled = false;
			$msg="Capturar Numero de Serie";
			break;
		}
		$totalserie=0;
		foreach($OrderLine->SerialItems as $Item){
			$totalserie=$totalserie+$Item->BundleQty ;
			/*if($Item->CostSerialItem==0 or $Item->CostSerialItem==''){
				$QuantityControlled =false;
				$msg="Capturar Costo de Serie";
				break;
			}*/
		}
		if ($totalserie<$OrderLine->Quantity){
			$QuantityControlled =false;
			$msg="Capturar Numero de Serie";
			break;
		}
		
	}
}

if ($QuantityControlled==false){
	$SQL = "UPDATE notesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
	$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la nota de credito no se pudo actualizar con el numero de factura');
	$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	prnMsg( $msg . ' ' . _('no ha sido posible realizar la transaccion, intentelo nuevamente'),'error');
	//echo '<p><a href="' . $rootpath . '/SelectNoteItems.php?' . SID .'identifier=' . $identifier .'"><b>'. _('Regresar a modificar la orden de venta') .'</a>';
	echo '<p><a href="' . $rootpath . '/SelectNoteItems.php?' . SID .'ModifyOrderNumber=' . $OrderNo .'"><b>'. _('Regresar a la Solicitud de Nota de Credito No.').' ' . $OrderNo .'</a>';
	include('includes/footer.inc');
	$Result = DB_Txn_Commit($db);	
	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);
	unset($_SESSION['CurrAbrev']);
	unset($_SESSION['Tagref']);
	exit;
}



if ($_SESSION['ExistingOrder']==0 or $Quotation!=0){
	$limitexuserxtag=LimitXUserXtagNC($_SESSION['Tagref'],$_SESSION['CurrAbrev'],$_SESSION['UserID'],$db);
	$totalNC=($_SESSION['Items'.$identifier]->total)+$TaxTotal;
	if ($limitexuserxtag<$totalNC){
		$SQL = "UPDATE notesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la nota de credito no se pudo actualizar con el numero de factura');
		$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		prnMsg( _('Ha excedido el monto definido por usuario para la generacion de notas de credito. Favor de verificar'),'error');
		echo '<p><a href="' . $rootpath . '/SelectNoteItems.php?' . SID .'ModifyOrderNumber=' . $OrderNo .'"><b>'. _('Regresar a la Solicitud de Nota de Credito No.').' ' . $OrderNo .'</a>';
		include('includes/footer.inc');
		$Result = DB_Txn_Commit($db);	
		unset($_SESSION['Items'.$identifier]->LineItems);
		unset($_SESSION['Items'.$identifier]);
		unset($_SESSION['CurrAbrev']);
		unset($_SESSION['Tagref']);
		exit;
	}
}


$SQL = 'UPDATE notesorders
        SET    quotation = 2
        WHERE notesorders.orderno=' . $OrderNo;
$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La actualizacion de la orden de nota de credito');
$DbgMsg = _('El SQL utilizado para la actualizacion de la nota de credito es');
$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);              
              
$SQL = "INSERT INTO debtortrans (transno,
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
                                alloc,
                                settled,
                                tagref,
                                currcode,
                                origtrandate,
								paymentname,
								nocuenta
                                
                )
        VALUES (". $CreditNo . ",
                " . $tiponota . ",
                '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
                '" . $_SESSION['Items'.$identifier]->Branch . "',
                '" . $DefaultDispatchDate . "',
                " . $PeriodNo . ", 'Inv-" . $OrderNo . "',
                '" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
                " . $OrderNo . ",
                " . -($_SESSION['Items'.$identifier]->total) . ",
                " . -$TaxTotal . ", " . -$_SESSION['Items'.$identifier]->FreightCost . ",
                " . $_SESSION['CurrencyRate'] . ",
                '" . $_POST['Comments'] . "',
                0,
                0,
                " . $_SESSION['Tagref'] .",
                '"  . $_SESSION['CurrAbrev']  . "',
				'"  . $fechaemision  . "',
 				'".$_POST['paymentname']."',
				'".$_POST['nocuenta']."'
                )";

$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la nota de credito para el cliente no se realizo');
$DbgMsg = _('El SQL utilizado para el registro de la nota de credito es:');
$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

$CreditTransID = DB_Last_Insert_ID($db,'debtortrans','id');
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
                                alloc,
                                settled,
                                tagref,
                                currcode,
                                origtrandate,
                                idgltrans)
        VALUES (". $CreditNo . ",
                " . $tiponota . ",
                '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
                '" . $_SESSION['Items'.$identifier]->Branch . "',
                '" . $DefaultDispatchDate . "',
                " . $PeriodNo . ", 'Inv-" . $OrderNo . "',
                '" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
                " . $OrderNo . ",
                " . -($_SESSION['Items'.$identifier]->total) . ",
                " . -$TaxTotal . ", " . -$_SESSION['Items'.$identifier]->FreightCost . ",
                " . $_SESSION['CurrencyRate'] . ",
                '" . $_POST['Comments'] . "',
                " . -$_SESSION['Items'.$identifier]->total . ",
                0,
                " . $_SESSION['Tagref'] .",
                '"  . $_SESSION['CurrAbrev']  . "',
                '"  . $fechaemision  . "',
                "  . $CreditTransID  . "
                )";
$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The customer credit note transaction could not be added to the database because');
$DbgMsg = _('The following SQL to insert the customer credit note was used');
$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

foreach ($TaxTotals AS $TaxAuthID => $TaxAmount) {
    $SQL = 'INSERT INTO debtortranstaxes (debtortransid,
                                            taxauthid,
                                            taxamount)
                    VALUES (' . $CreditTransID . ',
                            ' . $TaxAuthID . ',
                            ' . -($TaxAmount) . ')';
    
    $ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction taxes records could not be inserted because');
    $DbgMsg = _('The following SQL to insert the debtor transaction taxes record was used');
    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
}
//*********************************AQUI ESTA LA PARTE DETALLE DE LA NOTA DE CREDITO **********************************************
// Actualiza la orden de nota de credito a 2




foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
	$seriesventa="";
    //if ($OrderLine->QtyDispatched >0){
            //$LocalCurrencyPrice= round(($OrderLine->Price / $_SESSION['CurrencyRate']),2);
            $LocalCurrencyPrice= $OrderLine->Price;
            
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
            
                  $SQL = "UPDATE notesorderdetails
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
                                    SET locstock.quantity = locstock.quantity + " . $OrderLine->Quantity . "
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
                                    + stockmaster.overheadcost AS standard,
				    stockmaster.longdescription as descripcion
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
                    
                                   // $StandardCost += $AssParts['standard'];
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
				    
                                    if(isset($_SESSION['InvoiceNo']) and isset($_SESSION['InvoiceType']) and $_SESSION['InvoiceType']!=0){
					$SQL="Select standardcost
					      from stockmoves
					      where type='".$_SESSION['InvoiceType']."' and transno='".$_SESSION['InvoiceNo']."' and stockid='".$AssParts['component']."'";
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se puede recuperar el costo de los componentes y montaje de las cantidades ubicación de las existencias debido a ');
                                        $DbgMsg = _('El SQL que fallo es:');        
                                        $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
					 if (DB_num_rows($Result)==1){
                                                    $costXInvoice = DB_fetch_row($Result);
                                                    $EstimatedAvgCost = $costXInvoice[0];
                                        } else {
                                               if ($_SESSION['TypeCostStock']==1){
						    $EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$AssParts['component'], $db);
						}else{
						    $legalid=ExtractLegalid($_SESSION['Tagref'],$db);
						    $EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$AssParts['component'], $db);
						}   
                                        
					}      
				    }else{
					if ($_SESSION['TypeCostStock']==1){
					    $EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$AssParts['component'], $db);
					}else{
					    $legalid=ExtractLegalid($_SESSION['Tagref'],$db);
					    $EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$AssParts['component'], $db);
					}
				    }
				    // RECALCULAR COSTO PROMEDIO X LEGALID
				    $unitscostXlegal=StockAvgUnitsXLegal($AssParts['component'],$_SESSION['Tagref'],$db);
				    $unitsXLegal=StockUnitsXLegal($AssParts['component'],$_SESSION['Tagref'],$db);
				    $estavgcostXlegal=StockAvgcostXLegal($AssParts['component'],$_SESSION['Tagref'],$db);
				    $lastcostant=StockLastCostXLegal($AssParts['component'],$_SESSION['Tagref'],$db);
				    $EstimatedAvgCostXlegal=EstimatedAvgCostXLegal($AssParts['component'],$_SESSION['Tagref'],$unitscostXlegal,$estavgcostXlegal,$AssParts['quantity'],$EstimatedAvgCost,$lastcostant,0, $db);				
				    // RECALCULAR COSTO PROMEDIO X TAGREF
				    $unitscostXtag=StockAvgUnits($AssParts['component'],$_SESSION['Tagref'],$db);
				    $estavgcostXtag=StockAvgcost($AssParts['component'],$_SESSION['Tagref'],$db);
				    $lastcostantXtag=StockLastCost($AssParts['component'],$_SESSION['Tagref'],$db);
				    $unitsXtag=StockUnitsXTag($AssParts['component'],$_SESSION['Tagref'],$db);
				    $EstimatedAvgCostXtag=EstimatedAvgCost($AssParts['component'],$_SESSION['Tagref'],$unitscostXtag,$estavgcostXtag,$AssParts['quantity'],$EstimatedAvgCost,$lastcostantXtag,0, $db);
				    // CALCULO DEL COSTO DEL MOVIMIENTO
				    $StandardCost += ($EstimatedAvgCost * $AssParts['quantity']) ;
				    $OrderLine->StandardCost=$EstimatedAvgCost;
				    
                                    if ($Component_MBFlag=="M" OR $Component_MBFlag=="B"){
                                            $SQL = "INSERT INTO stockmoves (
                                                            stockid,
                                                            type,
                                                            transno,
                                                            loccode,
                                                            trandate,
                                                            debtorno,
                                                            branchcode,
                                                            prd,
                                                            reference,
                                                            qty,
                                                            standardcost,
                                                            show_on_inv_crds,
                                                            newqoh,
                                                            tagref,
                                                            avgcost)
                                            VALUES ('" . $AssParts['component'] . "',
                                                    " . $tiponota . ",
                                                    " . $CreditNo . ",
                                                    '" . $OrderLine->AlmacenStock . "',
                                                    '" . $DefaultDispatchDate . "',
                                                    '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
                                                    '" . $_SESSION['Items'.$identifier]->Branch . "',
                                                    " . $PeriodNo . ",
                                                    '" . _('Ex Inv') . ': ' .  $OrderNo . ' ' . _('armado') . ': ' . $OrderLine->StockID . "',
                                                    " . $AssParts['quantity'] * $OrderLine->Quantity . ",
                                                    " . $EstimatedAvgCost . ",
                                                    0,
                                                    " . ($QtyOnHandPrior + ($AssParts['quantity'] * $OrderLine->Quantity)) . "
                                                    ," . $_SESSION['Tagref'] . "," . $EstimatedAvgCost .")";
                                    } else {

                                            $SQL = "INSERT INTO stockmoves (
                                                            stockid,
                                                            type,
                                                            transno,
                                                            loccode,
                                                            trandate,
                                                            debtorno,
                                                            branchcode,
                                                            prd,
                                                            reference,
                                                            qty,
                                                            standardcost,
                                                            show_on_inv_crds,
                                                            tagref,
                                                            avgcost)
                                                    VALUES ('" . $AssParts['component'] . "',
                                                    " . $tiponota . ",
                                                    " . $CreditNo . ",
                                                    '" . $OrderLine->AlmacenStock . "',
                                                    '" . $DefaultDispatchDate . "',
                                                    '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
                                                    '" . $_SESSION['Items'.$identifier]->Branch . "',
                                                    " . $PeriodNo . ",
                                                    '" . _('Ex Inv') . ': ' . $OrderNo . ' ' . _('armado') . ': ' . $OrderLine->StockID . "',
                                                    " . $AssParts['quantity'] * $OrderLine->Quantity . ",
                                                    " . $EstimatedAvgCost . ",
                                                    0," . $_SESSION['Tagref'] . "," . $EstimatedAvgCost .")";
						    
                                    }
				    
                                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de existencias de los componentes de ensamble de'). ' '. $OrderLine->StockID . ' ' . _('no se pudieron registrar por que');
        			    $DbgMsg = _('El SQL para insertar componentes y montaje de los registros de movimientos de existencias es:');
                                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                    if ($Component_MBFlag=="M" OR $Component_MBFlag=="B"){
                                            $SQL = "UPDATE locstock
                                                    SET locstock.quantity = locstock.quantity + " . $AssParts['quantity'] * $OrderLine->QtyDispatched . "
                                                    WHERE locstock.stockid = '" . $AssParts['component'] . "'
                                                    AND loccode = '" . $OrderLine->AlmacenStock . "'";
                                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registro de las existencias del almacen no se puede actualizar para un componente de ensamble, por que');
				    $DbgMsg = _('El siguiente SQL para actualizar el registro de las existencias por almacen para el componente de ensamble es:');
                                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				    
				    // INSERTAR MOVIMIENTO CONTABLE POR CADA PRODUCTO DEL ENSAMBLE
						include('ProcessAssemblyNoteCredit.inc');
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
                    
		if(isset($_SESSION['InvoiceNo']) and isset($_SESSION['InvoiceType']) and $_SESSION['InvoiceType']!=0){
			$SQL="Select standardcost
			from stockmoves
			where type='".$_SESSION['InvoiceType']."' and transno='".$_SESSION['InvoiceNo']."' and stockid='".$OrderLine->StockID."'";
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se puede recuperar el costo de los componentes y montaje de las cantidades ubicación de las existencias debido a ');
			$DbgMsg = _('El SQL que fallo es:');
			//echo $SQL;
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			if (DB_num_rows($Result)==1){
				$costXInvoice = DB_fetch_row($Result);
				$EstimatedAvgCost = $costXInvoice[0];
			} else {
			if ($_SESSION['TypeCostStock']==1){
				$EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$OrderLine->StockID, $db);
			}else{
				$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
				$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$OrderLine->StockID, $db);
			}   
			
			}      
		}else{
			if ($_SESSION['TypeCostStock']==1){
				$EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$OrderLine->StockID, $db);
			}else{
				$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
				$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$OrderLine->StockID, $db);
			}
		}
                    
/* Insert stock movements for the stock coming back in - with unit cost */
                    if ($MBFlag=="M" OR $MBFlag=="B"){
				$OrderLine->StandardCost=$EstimatedAvgCost;
				// RECALCULAR COSTO PROMEDIO X LEGALID
				$unitscostXlegal=StockAvgUnitsXLegal($OrderLine->StockID,$_SESSION['Tagref'],$db);
				$unitsXLegal=StockUnitsXLegal($OrderLine->StockID,$_SESSION['Tagref'],$db);
				$estavgcostXlegal=StockAvgcostXLegal($OrderLine->StockID,$_SESSION['Tagref'],$db);
				$lastcostant=StockLastCostXLegal($OrderLine->StockID,$_SESSION['Tagref'],$db);
				$EstimatedAvgCostXlegal=EstimatedAvgCostXLegal($OrderLine->StockID,$_SESSION['Tagref'],$unitscostXlegal,$estavgcostXlegal,$OrderLine->Quantity,$EstimatedAvgCost,$lastcostant,0, $db);				
				// RECALCULAR COSTO PROMEDIO X TAGREF
				$unitscostXtag=StockAvgUnits($OrderLine->StockID,$_SESSION['Tagref'],$db);
				$estavgcostXtag=StockAvgcost($OrderLine->StockID,$_SESSION['Tagref'],$db);
				$lastcostantXtag=StockLastCost($OrderLine->StockID,$_SESSION['Tagref'],$db);
				$unitsXtag=StockUnitsXTag($OrderLine->StockID,$_SESSION['Tagref'],$db);
				$EstimatedAvgCostXtag=EstimatedAvgCost($OrderLine->StockID,$_SESSION['Tagref'],$unitscostXtag,$estavgcostXtag,$OrderLine->Quantity,$EstimatedAvgCost,$lastcostantXtag,0, $db);
				
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
                                            narrative,
                                            tagref,
                                            avgcost)
                                    VALUES ('" . $OrderLine->StockID . "',
                                            " . $tiponota . ",
                                            " . $CreditNo . ",
                                            '" . $OrderLine->AlmacenStock . "',
                                            '" . $DefaultDispatchDate . "',
                                            '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
                                            '" . $_SESSION['Items'.$identifier]->Branch . "',
                                            " . $LocalCurrencyPrice . ",
                                            " . $PeriodNo . ",
                                            '" . _('Ex Inv') .' - ' . $OrderNo . "',
                                            " . $OrderLine->Quantity . ",
                                            " . $OrderLine->DiscountPercent . ",
                                            " . $OrderLine->DiscountPercent1 . ",
                                            " . $OrderLine->DiscountPercent2 . ",
                                            " . $montodescuento . ", 
                                            " . $OrderLine->StandardCost . ",
                                            " .  ($QtyOnHandPrior + $OrderLine->Quantity) . ",
                                            '" . DB_escape_string(htmlspecialchars_decode($OrderLine->Narrative,ENT_NOQUOTES)) . "'," .
                                            $_SESSION['Tagref'] . "," . $EstimatedAvgCost.")";
                    } else {

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
                                    narrative,
                                    tagref,
                                    avgcost)
                            VALUES ('" . $OrderLine->StockID . "',
                                    " . $tiponota . ",
                                    " . $CreditNo . ",
                                    '" . $OrderLine->AlmacenStock . "',
                                    '" . $DefaultDispatchDate . "',
                                    '" . $_SESSION['CreditItems']->DebtorNo . "',
                                    '" . $_SESSION['CreditItems']->Branch . "',
                                    " . $LocalCurrencyPrice . ",
                                    " . $PeriodNo . ",
                                    '" . _('Ex Inv') .' - ' . $OrderNo . "',
                                    " . $OrderLine->Quantity . ",
                                    " . $OrderLine->DiscountPercent . ",
                                    " . $OrderLine->DiscountPercent1 . ",
                                    " . $OrderLine->DiscountPercent2 . ",
                                    " . $montodescuento . ", 
                                    " . $OrderLine->StandardCost . ",
                                    '" . DB_escape_string(htmlspecialchars_decode($OrderLine->Narrative,ENT_NOQUOTES)) . "',".
                                    $_SESSION['Tagref'] . "," . $EstimatedAvgCost.")";
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
                                            $SQL = "INSERT INTO stockserialitems (stockid, loccode, serialno, quantity,standardcost)
                                                    VALUES
                                                    ('" . $OrderLine->StockID . "', '" . $OrderLine->AlmacenStock . "', '" . $Item->BundleRef . "',  ". $Item->BundleQty .",".$Item->CostSerialItem.")";

                                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
                                            $DbgMsg = _('The following SQL to update the serial stock item record was used');
                                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                    } else {

                                            $SQL = "UPDATE stockserialitems
                                                    SET quantity= quantity + " . $Item->BundleQty . "
                                                    WHERE stockid='" . $OrderLine->StockID . "'
                                                    AND loccode='" . $OrderLine->AlmacenStock . "'
                                                    AND serialno='" . $Item->BundleRef . "'";
                                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
                                            $DbgMsg = _('The following SQL to update the serial stock item record was used');
                                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                    }
				    $seriesventa= $seriesventa. " |No Serie:".$Item->BundleRef;	
                                    /* now insert the serial stock movement */
                                   $totalserie=$totalserie+$Item->CostSerialItem;
				  // echo 'costo:'.$Item->CostSerialItem;
				    $totalseriessol=$totalseriessol+$Item->BundleQty;
				    $OrderLine->StandardCost=$totalserie/$totalseriessol;

                                    $SQL = "INSERT INTO stockserialmoves (stockmoveno, stockid, serialno, moveqty) VALUES (" . $StkMoveNo . ", '" . $OrderLine->StockID . "', '" . $Item->BundleRef . "', " . $Item->BundleQty . ")";
                                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('El movimiento del numero de serie no se pudo recuperar');
                                    $DbgMsg = _('El SQL utilizado es');
                                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                            }/* foreach controlled item in the serialitems array */
                    } /*end if the orderline is a controlled item */

            } 
            /*Get the ID of the StockMove... */
            
	    
	     if(isset($_SESSION['InvoiceNo']) and isset($_SESSION['InvoiceType']) and $_SESSION['InvoiceType']!=0 and $OrderLine->Controlled !=1){
		$OrderLine->StandardCost=$EstimatedAvgCost;
	     }
          
	    
	
		$SQL = "UPDATE stockmoves
			SET standardcost=  " . $OrderLine->StandardCost . ",
			avgcost=" . $OrderLine->StandardCost . "
			WHERE stkmoveno=" . $StkMoveNo ;
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede actualizar por que');
		$DbgMsg = _('El siguiente SQL para actualizar el numero de serie del stock es:');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	    
            /*Insert the taxes that applied to this line */
            foreach ($OrderLine->Taxes as $Tax) {
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
                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Los impuestos y tasas aplicables a esta partida de la factura, no pueden insertarse, por que');
		    $DbgMsg = _('El siguiente SQL para insertar los registros de detalle de valores del impuesto es:');
                    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            }
/*Insert Sales Analysis records */
            $SQL="SELECT COUNT(*),
                    stkcategory,
                    salesanalysis.area,
                    salesperson
            FROM salesanalysis,
                    custbranch,
                    stockmaster
            WHERE salesanalysis.stkcategory=stockmaster.categoryid
            AND salesanalysis.stockid=stockmaster.stockid
            AND salesanalysis.cust=custbranch.debtorno
            AND salesanalysis.custbranch=custbranch.branchcode
            AND salesanalysis.area=custbranch.area
            AND salesanalysis.salesperson=custbranch.salesman
            AND typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
            AND periodno=" . $PeriodNo . "
            AND cust = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
            AND custbranch = '" . $_SESSION['Items'.$identifier]->Branch . "'
            AND salesanalysis.stockid = '" . $OrderLine->StockID . "'
            AND budgetoractual=1
            GROUP BY stkcategory, salesanalysis.area, salesperson";
            $ErrMsg = _('El recuento de registros actuales para el analisis de ventas no se pudo recuperar por que');
	    $DbgMsg = '<br>'. _('El SQL para contar el numero de registros de analisis de ventas es:');
            $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
            $myrow = DB_fetch_row($Result);
            if ($myrow[0]>0){  /*Update the existing record that already exists */

                    if ($_POST['CreditType']=='ReverseOverCharge'){

                            $SQL = "UPDATE salesanalysis
                                    SET amt=amt-" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
                                    disc=disc-" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . "
                                    WHERE salesanalysis.area='" . $myrow[2] . "'
                                    AND salesanalysis.salesperson='" . $myrow[3] . "'
                                    AND typeabbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "'
                                    AND periodno = " . $PeriodNo . "
                                    AND cust = '" . $_SESSION['CreditItems']->DebtorNo . "'
                                    AND custbranch = '" . $_SESSION['CreditItems']->Branch . "'
                                    AND stockid = '" . $OrderLine->StockID . "'
                                    AND salesanalysis.stkcategory ='" . $myrow[1] . "'
                                    AND budgetoractual=1";

                    } else {

                            $SQL = "UPDATE salesanalysis
                                    SET amt=amt-" . ($OrderLine->Price * $OrderLine->Quantity / $_SESSION['CurrencyRate']) . ",
                                    cost=cost-" . ($OrderLine->StandardCost * $OrderLine->Quantity) . ",
                                    qty=qty-" . $OrderLine->Quantity . ",
                                    disc=disc-" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . "
                                    WHERE salesanalysis.area='" . $myrow[2] . "'
                                    AND salesanalysis.salesperson='" . $myrow[3] . "'
                                    AND typeabbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "'
                                    AND periodno = " . $PeriodNo . "
                                    AND cust = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
                                    AND custbranch = '" . $_SESSION['Items'.$identifier]->Branch . "'
                                    AND stockid = '" . $OrderLine->StockID . "'
                                    AND salesanalysis.stkcategory ='" . $myrow[1] . "'
                                    AND budgetoractual=1";
                    }

            } else { /* insert a new sales analysis record */

                    if ($_POST['CreditType']=='ReverseOverCharge'){

                            $SQL = "INSERT INTO salesanalysis (typeabbrev,
                                                    periodno,
                                                    amt,
                                                    cust,
                                                    custbranch,
                                                    qty,
                                                    disc,
                                                    stockid,
                                                    area,
                                                    budgetoractual,
                                                    salesperson,
                                                    stkcategory)
                                    SELECT '" . $_SESSION['CreditItems']->DefaultSalesType . "',
                                            " . $PeriodNo . ",
                                            " . -($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
                                            '" . $_SESSION['CreditItems']->DebtorNo . "',
                                            '" . $_SESSION['CreditItems']->Branch . "',
                                            0,
                                            " . -($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
                                            '" . $OrderLine->StockID . "',
                                            custbranch.area,
                                            1,
                                            custbranch.salesman,
                                            stockmaster.categoryid
                                    FROM stockmaster,
                                            custbranch
                                    WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
                                    AND custbranch.debtorno = '" . $_SESSION['CreditItems']->DebtorNo . "'
                                    AND custbranch.branchcode='" . $_SESSION['CreditItems']->Branch . "'";
                    } else {

                            $SQL = "INSERT INTO salesanalysis (typeabbrev,
                                            periodno,
                                            amt,
                                            cost,
                                            cust,
                                            custbranch,
                                            qty,
                                            disc,
                                            stockid,
                                            area,
                                            budgetoractual,
                                            salesperson,
                                            stkcategory)
                                    SELECT '" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
                                            " . $PeriodNo . ", " . -($OrderLine->Price * $OrderLine->Quantity / $_SESSION['CurrencyRate']) . ",
                                            " . -($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ",
                                            '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
                                            '" . $_SESSION['Items'.$identifier]->Branch . "',
                                            " . -$OrderLine->Quantity . ",
                                            " . -($montodescuento) . ",
                                            '" . $OrderLine->StockID . "',
                                            custbranch.area,
                                            1,
                                            custbranch.salesman,
                                            stockmaster.categoryid
                                    FROM stockmaster,
                                            custbranch
                                    WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
                                    AND custbranch.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
                                    AND custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'";
                    }
            }
            $ErrMsg = _('El analisis de ventas no se pudo registrar por que');
            $DbgMsg = _('El siguiente SQL para actualizar el analisis de ventas es:');
            $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
    /* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/
            if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $OrderLine->StandardCost !=0 and $MBFlag!='A'
                    AND $_POST['CreditType']!='ReverseOverCharge'){

/*first the cost of sales entry*/

                    $COGSAccount = GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['CreditItems']->DefaultSalesType, $db);

                    $SQL = "INSERT INTO gltrans (type,
                                            typeno,
                                            trandate,
                                            periodno,
                                            account,
                                            narrative,
                                            amount,
                                            tag)
                            VALUES (11,
                                    '" . $CreditNo . "',
                                    '" . $DefaultDispatchDate . "',
                                    '" . $PeriodNo . "',
                                    '" . $COGSAccount . "',
                                    '" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID ." | ".$OrderLine->ItemDescription . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "|" . $seriesventa. "',
                                    '" . -round($OrderLine->StandardCost * $OrderLine->Quantity,2) . "'
                                    ,'" . $_SESSION['Tagref'] . "')";
                    if($_SESSION['UserID'] == "admin"){
                    	//echo '<pre>'.$SQL;
                    }
                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
                    $DbgMsg = _('The following SQL to insert the GLTrans record was used');
                    $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

                    /*now the stock entry*/


                    if ($_POST['CreditType']=='WriteOff'){
                            $SQL = "INSERT INTO gltrans (type,
                                                    typeno,
                                                    trandate,
                                                    periodno,
                                                    account,
                                                    narrative,
                                                    amount, tag)
                                    VALUES (11,
                                            '" . $CreditNo . "',
                                            '" . $DefaultDispatchDate . "',
                                            '" . $PeriodNo . "',
                                            '" . $_POST['WriteOffGLCode'] . "',
                                            '" . $_SESSION['CreditItems']->DebtorNo . " - " . $OrderLine->StockID ." | ".$OrderLine->ItemDescription . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "|" . $seriesventa.  "',
                                            '" . round($OrderLine->StandardCost * $OrderLine->QtyDispatched,2) . "',
                                            '" . $_SESSION['Tagref'] . "')";
                    } else {
                            $StockGLCode = GetStockGLCode($OrderLine->StockID, $db);
                            $SQL = "INSERT INTO gltrans (type,
                                                    typeno,
                                                    trandate,
                                                    periodno,
                                                    account,
                                                    narrative,
                                                    amount, tag)
                                    VALUES ('". $tiponota  . "',
                                            '" . $CreditNo . "',
                                            '" . $DefaultDispatchDate . "',
                                            '" . $PeriodNo . "',
                                            '" . $StockGLCode['stockact'] . "',
                                            '" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID ." | ". $OrderLine->ItemDescription . " x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . "|" . $seriesventa.  "',
                                            '" . round($OrderLine->StandardCost * $OrderLine->Quantity,2) . "',
                                            '" . $_SESSION['Tagref'] . "')";
                    }
                    if($_SESSION['UserID'] == "admin"){
                    	//echo '<pre>'.$SQL;
                    }
                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
		    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
                    $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

            } /* end of if GL and stock integrated and standard cost !=0 */
	        
            if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $OrderLine->Price !=0){
            //Post sales transaction to GL credit sales
                    $SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
                    $SQL = "INSERT INTO gltrans (type,
                                            typeno,
                                            trandate,
                                            periodno,
                                            account,
                                            narrative,
                                            amount, tag)
                            VALUES ('" . $tiponota . "',
                                    '" . $CreditNo . "',
                                    '" . $DefaultDispatchDate . "',
                                    '" . $PeriodNo . "',
                                    '" . $SalesGLAccounts['salesglcode'] . "',
                                    '" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID ." | ".$OrderLine->ItemDescription. " x " . $OrderLine->Quantity . " @ " . $OrderLine->Price . "|" . $seriesventa.  "',
                                    '" . round(($OrderLine->Price * $OrderLine->Quantity)/$_SESSION['CurrencyRate'],2) . "'
                                    ,'" . $_SESSION['Tagref'] . "')";

                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
		    $DbgMsg = '<br>' ._('El siguiente SQL para insertar en GLTrans es:');
		    if($_SESSION['UserID'] == "admin"){
		    	//echo '<pre>'.$SQL;
		    }
		    		$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
                    // extrae el total de descuentos en cascada
                    $totalsindescuento=($OrderLine->Price * $OrderLine->Quantity)/$_SESSION['CurrencyRate'];
                    $montodescuento= (($OrderLine->Quantity * $OrderLine->Price)/$_SESSION['CurrencyRate']) * (1 - $OrderLine->DiscountPercent);
                    $montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent1);
                    $montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent2);
                    $montodescuento=$totalsindescuento-$montodescuento;
                    
                    // concatena el narrative que justifica contablemente el descuento en casdada
                    //$textodescuento= $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID;
		    $textodescuento= $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID ." | ".$OrderLine->ItemDescription;	
                    $textodescuento= $textodescuento . " @ " . ($OrderLine->DiscountPercent * 100). " % ";
                    $textodescuento= $textodescuento . " @ " . ($OrderLine->DiscountPercent1 * 100)." % ";
                    $textodescuento= $textodescuento . " @ " . ($OrderLine->DiscountPercent2 * 100)." % ";
		    $textodescuento= $textodescuento . " | " . $seriesventa;			
		    if ($montodescuento!=0 ){

                    //if ($OrderLine->DiscountPercent !=0){

                            $SQL = "INSERT INTO gltrans (type,
                                                    typeno,
                                                    trandate,
                                                    periodno,
                                                    account,
                                                    narrative,
                                                    amount, tag)
                                    VALUES ('" . $tiponota . "',
                                            '" . $CreditNo . "',
                                            '" . $DefaultDispatchDate . "',
                                            '" . $PeriodNo . "',
                                            '" . $SalesGLAccounts['discountglcode'] . "',
                                            '" . $textodescuento."',
                                            '" . -round($montodescuento,2) . "'
                                            ,'" . $_SESSION['Tagref'] . "')";
                            if($_SESSION['UserID'] == "admin"){
                            	//echo '<pre>'.$SQL;
                            }
                            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El descuento de la venta no se ha insertado, por que');
			    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
                            $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
                    } /*end of if discount !=0 */
            } /*end of if sales integrated with debtors */
  //  } /*Quantity dispatched is more than 0 */
} /*end of OrderLine loop */

if ($_SESSION['CompanyRecord']['gllink_debtors']==1){
/*Post credit note transaction to GL credit debtors, debit freight re-charged and debit sales */
$tipocliente=ExtractTypeClient($_SESSION['Items'.$identifier]->DebtorNo,$db);
$cuentacargo=ClientAccount($tipocliente,'gl_accountsreceivable',$db);//$_SESSION['CompanyRecord']['debtorsact'];

    if (($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost + $TaxTotal) !=0) {
            $SQL = "INSERT INTO gltrans (type,
                                            typeno,
                                            trandate,
                                            periodno,
                                            account,
                                            narrative,
                                            amount, tag)
                            VALUES ('" . $tiponota . "',
                                    '" . $CreditNo . "',
                                    '" . $DefaultDispatchDate . "',
                                    '" . $PeriodNo . "',
                                    '" . $cuentacargo . "',
                                    '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
                                    '" . -round(($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost + $TaxTotal)/$_SESSION['CurrencyRate'],2) . "'
                            ," . $_SESSION['Tagref'] . ")";
           $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La cuenta por cobrar no se ha insertado, por que');
	    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
	    if($_SESSION['UserID'] == "admin"){
	    	//echo '<pre>'.$SQL;
	    }
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
                    VALUES (11,
                            '" . $CreditNo . ",
                            '" . $DefaultDispatchDate . "',
                            '" . $PeriodNo . "',
                            '" . $_SESSION['CompanyRecord']['freightact'] . "',
                            '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
                            '" . round($_SESSION['Items'.$identifier]->FreightCost/$_SESSION['CurrencyRate'],2) . "'
                            ,'" . $_SESSION['Tagref'] . "')";

            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The freight GL posting for this credit note could not be inserted because');
            $DbgMsg = _('The following SQL to insert the GLTrans record was used');
            if($_SESSION['UserID'] == "admin"){
            	//echo '<pre>'.$SQL;
            }
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
                                    '" . $tiponota . "', 
                                    '" . $CreditNo . "', 
                                    '" . $DefaultDispatchDate . "', 
                                    '" . $PeriodNo . "', 
                                    '" . $TaxGLCodes[$TaxAuthID] . "', 
                                    '" . $_SESSION['Items'.$identifier]->DebtorNo . "', 
                                    '" . ($TaxAmount/$_SESSION['CurrencyRate']) . "',
                                    '" . $_SESSION['Tagref'] . "')";
                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El impuesto de la venta no se ha insertado, por que');
		    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
		    if($_SESSION['UserID'] == "admin"){
		    	//echo '<pre>'.$SQL;
		    }
                    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            }
    }
}
//*********************************FIN DE DETALLE DE LA NOTA DE CREDITO ******************************************************
//
?>




