<?php

//echo "<br>entra".$OrderLine->StandardCost;
    //$COGSAccount = GetCOGSGLAccount($Area, $AssParts['component'], $_SESSION['CreditItems']->DefaultSalesType, $db);
 $tipoalmacen = GetTypeLocation($OrderLine->AlmacenStock,$db);
			//echo 'tipoalmacen:'.$tipoalmacen.'<br>';
	if ($tipoalmacen!=2){
	     $COGSAccount=GetCOGSGLAccount($Area, $AssParts['component'], $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
	}else{
	     $COGSAccount=GetCOGSGLAccountConsigment($Area, $AssParts['component'], $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
	}
    $SQL = "INSERT INTO gltrans (type,
                            typeno,
                            trandate,
                            periodno,
                            account,
                            narrative,
                            amount,
                            tag)
            VALUES ( " . $tiponota . ", 
                    " . $CreditNo . ",
                    '" . $DefaultDispatchDate . "',
                    " . $PeriodNo . ",
                    " . $COGSAccount . ",
                    '" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $AssParts['component'] ." | ".$AssParts['descripcion']. " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "|" . $seriesventa. "',
                    " . -round($OrderLine->StandardCost * $OrderLine->Quantity,2) . "
                    ," . $_SESSION['Tagref'] . ")";

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
                    VALUES ( " . $tiponota . ", 
                            " . $CreditNo . ",
                            '" . $DefaultDispatchDate . "',
                            " . $PeriodNo . ",
                            " . $_POST['WriteOffGLCode'] . ",
                            '" . $_SESSION['CreditItems']->DebtorNo . " - " . $AssParts['component'] ." | ".$AssParts['descripcion'] . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "|" . $seriesventa.  "',
                            " . round($OrderLine->StandardCost * $OrderLine->Quantity,2) . ",
                            " . $_SESSION['Tagref'] . ")";
    } else {
            $StockGLCode = GetStockGLCode($AssParts['component'], $db);
             if ($tipoalmacen!=2){
			$cuentaalmacen=$StockGLCode['stockact'];
		}else{
			$cuentaalmacen=$StockGLCode['stockconsignmentact'];
		}
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
                            " . $cuentaalmacen . ",
                            '" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $AssParts['component'] ." | ". $AssParts['descripcion']. " x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . "|" . $seriesventa.  "',
                            " . round($OrderLine->StandardCost * $OrderLine->Quantity,2) . ",
                            " . $_SESSION['Tagref'] . ")";
    }

    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
    $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
?>
