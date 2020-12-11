<?php
// primero ordeno los proveedores para hacer las compras
$Total=$_POST['Total'];
$proveedores = array();
$NewItem_array=array();
$indice=0;
for ($i=1;$i <= $Total;$i++){
    if($_POST['Sel' . $i]==true ){
	if ($_POST['CantidadT_'.$i]>0 and strlen($_POST['CantidadT_'.$i])>0){
	    $proveedores[$indice]['supplier']=$_POST['supplierid_' . $i];
	    //$NewItem_array[$key] = $_POST['StockLocationDist_'.$i];
	    $proveedores[$indice]['Cantidad']=$_POST['CantidadT_'.$i];
	    $proveedores[$indice]['qtyinvoiced']=$_POST['qtyinvoiced_' . $i];
	    $proveedores[$indice]['Producto']=$_POST['itemcode_' . $i];
	    $proveedores[$indice]['Descripcion']=$_POST['itemdescription_' . $i];
	    $proveedores[$indice]['porccompra']=$_POST['porccompra_' . $i];
	    //echo 'porcompra:'.'porccompra__' . $i.':'.$_POST['porccompra_' . $i];
	    $proveedores[$indice]['totalporccompra']=$_POST['totalporccompra_' . $i];
	    $proveedores[$indice]['porcpartida']=$_POST['porcpartida_' . $i];
	    $proveedores[$indice]['totalporcpartida']=$_POST['totalporcpartida_' . $i];
	    $proveedores[$indice]['Almacen']=$_POST['LocationReq_' . $i];
	    $proveedores[$indice]['moneda']=$_POST['moneda_' . $i];
	    $proveedores[$indice]['rate']=$_POST['rate_' . $i];
	    $proveedores[$indice]['price']=$_POST['price_' . $i];
	    $proveedores[$indice]['orderno']=$_POST['orderno_' . $i];
	    $proveedores[$indice]['podetailitem']=$_POST['podetailitem_' . $i];
	    
	    $indice=$indice+1;
	}
    }
}
//ordena el arreglo por proveedor que es el primer indice
sort($proveedores);
$totalunidadescompra=0;
$OrdenANT=0;
$OrdenIni=-1;
//Validar que todas las partidas tengan porcentaje de devolucion valido
foreach ($proveedores as $key => $val) {
    //echo "entra";
     $ProveedorCompra=$val['supplier'];
   // echo 'entraaa'.$val['porccompra'];
    if ($val['porccompra']>0){
       //cuenta cuantos productos tiene la orden y hace lo proporcional por partida de venta
       $val['porcpartida']=0;
       $val['totalporcpartida']=0;
       //$unidadesdev=$val['qtyinvoiced']*(($val['porccompra']-$val['totalporccompra'])/100);
       $totalunidadescompra=$totalunidadescompra+$val['Cantidad'];
       $unidadesfacturadas=$unidadesfacturadas+$val['qtyinvoiced'];
    }else{
	   $val['totalporccompra']=0;
	   $val['porccompra']=0;
	   $unidadesdev=$val['qtyinvoiced']*(($val['porcpartida'])/100);
	  // echo $val['porcpartida'];
	  // $unidadesdev=0;
    }
    //valida que tenga existencias el producto   
    $SQL = "select quantity AS disponible
	    FROM  locstock
	    WHERE locstock.stockid = '" .$val['Producto'] . "'
	    AND loccode = '" . $val['Almacen'] . "'";
    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El stock por almacen no se puede modificar');
    $DbgMsg = _('El SQL para actualizar el registro de las existencias del almacen es:');
    $Resultdisponible = DB_query($SQL, $db, $ErrMsg,$DbgMsg,true);
    $CheckNegRow = DB_fetch_array($Resultdisponible);
    if ($CheckNegRow['disponible'] < $val['Cantidad']){
	    prnMsg( _('El Producto '). $val['Producto'].' - '.$val['Descripcion']. _(' no cuenta con las existencias necesarias para realizar la devoclucion.Verifique'),'error');
	    include('includes/footer.inc');
	    exit;
    }
    
    if ($val['porccompra']>0){
	
	if($OrdenANT!=$val['orden'] and $OrdenANT>0){
	    $unidadesdevueltas=$totalunidadescompra;
	    $unidadesdev=$unidadesfacturadas*(($porcANT- $totalANT)/100);
	    if($unidadesdev<$unidadesdevueltas){
	       prnMsg( _('La Compra '). $OrdenANT. _(' sobrepasa el porcentaje de devolucion.Verifique'),'error');
	       include('includes/footer.inc');
	       exit;
	    }
	    $totalunidadescompra=0;
	    $unidadesfacturadas=0;
	}
    }else{
	$unidadesdevueltas=$val['Cantidad'];
	//$unidadesdev=$unidadesfacturadas*($val['porcpartida']/100);
	if($unidadesdev<$unidadesdevueltas){
	    prnMsg( _('El Producto '). $val['Producto'].' - '.$val['Descripcion'].$unidadesdev.' - '.$unidadesdevueltas. _(' sobrepasa el porcentaje de devolucion.Verifique'),'error');
	    include('includes/footer.inc');
	    exit;
	}
    }
   
    $OrdenIni=$OrdenIni+1;
    $OrdenANT=$val['orderno'];
    $porcANT=$val['porccompra'];
    $totalANT=$val['totalporccompra'];
    
    
}

if($OrdenIni==0){
    foreach ($proveedores as $key => $val) {
    //echo "entra";
     $ProveedorCompra=$val['supplier'];
   // echo 'entraaa'.$val['porccompra'];
    if ($val['porccompra']>0){
       //cuenta cuantos productos tiene la orden y hace lo proporcional por partida de venta
       $val['porcpartida']=0;
       $val['totalporcpartida']=0;
       //$unidadesdev=$val['qtyinvoiced']*(($val['porccompra']-$val['totalporccompra'])/100);
       $totalunidadescompra=$totalunidadescompra+$val['Cantidad'];
       $unidadesfacturadas=$unidadesfacturadas+$val['qtyinvoiced'];
    }else{
	   $val['totalporccompra']=0;
	   $val['porccompra']=0;
	   $unidadesdev=$val['qtyinvoiced']*(($val['porcpartida'])/100);
	  // echo $val['porcpartida'];
	  // $unidadesdev=0;
    }
   
    
    if ($val['porccompra']>0){
	
	if($OrdenANT!=$val['orden']){
	    $unidadesdevueltas=$totalunidadescompra;
	    $unidadesdev=$unidadesfacturadas*(($porcANT- $totalANT)/100);
	    if($unidadesdev<$unidadesdevueltas){
	       prnMsg( _('La Compra '). $OrdenANT. _(' sobrepasa el porcentaje de devolucion.Verifique'),'error');
	       include('includes/footer.inc');
	       exit;
	    }
	    $totalunidadescompra=0;
	    $unidadesfacturadas=0;
	}
    }else{
	$unidadesdevueltas=$val['Cantidad'];
	//$unidadesdev=$unidadesfacturadas*($val['porcpartida']/100);
	if($unidadesdev<$unidadesdevueltas){
	    prnMsg( _('El Producto '). $val['Producto'].' - '.$val['Descripcion'].$unidadesdev.' - '.$unidadesdevueltas. _(' sobrepasa el porcentaje de devolucion.Verifique'),'error');
	    include('includes/footer.inc');
	    exit;
	}
    }
   
    $OrdenIni=$OrdenIni+1;
    $OrdenANT=$val['orderno'];
    $porcANT=$val['porccompra'];
    $totalANT=$val['totalporccompra'];
    
    
    }
    
}

//inicializa variables
$ProveedorCompraANT=0;
$CreditNo=0;
$entra=false;
$entramail=false;
$tiponota=45;
$totalProveedor=0;
$TaxTotal=0;
$MonedaANT='';
$rateANT=0;
$fechaemision=date("Y-m-d H:i:s");
// inicializa transacciones de bd
$Result = DB_Txn_Begin($db);

$fechaemision=date("Y-m-d H:i:s");
$PeriodNo = GetPeriod($fechaemision, $db);
$OrderNo=0;
foreach ($proveedores as $key => $val) {
  //  echo "entra";
    $ProveedorCompra=$val['supplier'];
    //Se inserta una nota de credito Full para este proveedor
    if ($ProveedorCompraANT!=$ProveedorCompra){
	if ($CreditNo>0){
	    //inserta monto de supptrans
	    $fechaemision=date("Y-m-d H:i:s");
	    $PeriodNo = GetPeriod($fechaemision, $db);
	    $entra=true;
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
			    " . $_POST['UnidNeg'] .",
			    " . $tiponota . ",
			    '" . $ProveedorCompraANT. "',
			    '" . $fechaemision . "',
			    '" . $fechaemision . "',
			    " . -($totalProveedor) . ",
			    " . -$TaxTotal . ",
			    " . $OrderNo . ",
			    " . $rateANT. ",
			    '" . _('Nota de Devolucion de productos que registro ') .$_SESSION['UserID'].' '.$PeriodNo . 'Trans-' . $CreditNo . "',
			    '"  . $MonedaANT  . "',
			    '"  . $fechaemision  . "'
			    
	    
			    )";
	    $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la nota de credito para el proveedor no se realizo');
	    $DbgMsg = _('El SQL utilizado para el registro de la nota de credito es:');
	    $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
	    $CreditTransID = DB_Last_Insert_ID($db,'supptrans','id');
	    // inserta movimiento contable
	    $tipoproveedor = ExtractTypeSupplier($ProveedorCompraANT,$db);
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
                                    '" . $fechaemision . "',
                                    " . $PeriodNo . ",
                                    " . $ctaxtipoproveedor . ",
                                    '" . $ProveedorCompraANT. "',
                                    " . round(($totalProveedor)/$rateANT,2) . "
                            ," . $_POST['UnidNeg'] . ")";
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La cuenta por cobrar no se ha insertado, por que');
	    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
            $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
	    //inserta movimiento de impuesto
	    if($TaxTotal>0){
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
				    " . $Taxglcode . ", 
				    '" . $ProveedorCompraANT . "', 
				    " . -($TaxTotal/$rateANT) . ",
				    " .  $_POST['UnidNeg']. ")";
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El impuesto de la venta no se ha insertado, por que');
		$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	    }
	    
	    $liga="PDFNoteReturns.php?&clave=devolucion";
	    $liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PDFNoteReturns.php?OrderNo='.$OrderNo.'&TransNo=' . $CreditNo .'&Type='.$tiponota.'&Tagref='.$_POST['UnidNeg'].'">'. _('Imprimir Nota Devolucion') . ' (' . _('Laser') . ')' .'</a>';
	    echo '<br>'.$liga.'<br>';
	}
	$TaxTotal=0;
	$totalProveedor=0;
	//extrae numero de transaccion
	$CreditNo = GetNextTransNo(45, $db);
    }
    $preciocompra=$val['price'];
    //genera el movimiento de inventario
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
		    price,
		    newqoh,
		    tagref)
    VALUES ('" . $val['Producto'] . "',
	    " . $tiponota . ",
	    " . $CreditNo . ",
	    '" . $val['Almacen']. "',
	    '" . $fechaemision . "',
	    " . $PeriodNo . ",
	    '" . _('Nota Credito Proveedor').' : '.$ProveedorCompra. ' : ' . $CreditNo .': ' . $val['Producto'] . "',
	    " . -($val['Cantidad']) . ",
	    " . $preciocompra . ",
	    " . $preciocompra . ",
	    " . ($val['Cantidad']) . ",
	    " . $_POST['UnidNeg']. ")";
    $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la nota de credito para el proveedor no se realizo');
    $DbgMsg = _('El SQL utilizado para el registro del movimiento la nota de devolucion es:');
    $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
    // quita las existencias de ese almacen
    $SQL = "UPDATE locstock
	    SET locstock.quantity = locstock.quantity - " . $val['Cantidad'] . "
	    WHERE locstock.stockid = '" .$val['Producto'] . "'
	    AND loccode = '" . $val['Almacen'] . "'";
    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El stock por almacen no se puede modificar');
    $DbgMsg = _('El SQL para actualizar el registro de las existencias del almacen es:');
    $Result = DB_query($SQL, $db, $ErrMsg,$DbgMsg,true);
    //extrae el precio de compra de este producto
    $preciocompra=$val['price'];
    //genera el moviemitnto contable
    $StockGLCode = GetStockGLCode($val['Producto'], $db);
    $SQL = "INSERT INTO gltrans (type,
			    typeno,
			    trandate,
			    periodno,
			    account,
			    narrative,
			    amount, tag)
	    VALUES (". $tiponota  . ",
		    " . $CreditNo . ",
		    '" . $fechaemision . "',
		    " . $PeriodNo . ",
		    " . $StockGLCode['stockact'] . ",
		    '" . $ProveedorCompra . " - " . $val['Producto'] . " x " . $val['Cantidad'] . " @ " . $preciocompra . "',
		    " . -round(($val['Cantidad']*$preciocompra),2) . ",
		    " . $_POST['UnidNeg']. ")";
    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
    $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
    $totalProveedor=$totalProveedor+($val['Cantidad']*$preciocompra);
    //trae el impuesto del producto y lo suma al total del proveedor
    $sql = "SELECT taxcatid,minimummarginsales,stockmaster.barcode
	    FROM stockmaster inner join stockcategory on stockcategory.categoryid=stockmaster.categoryid
	    WHERE stockid='".$val['Producto']."'";
    $TaxCatQuery = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
    if ($TaxCatRow = DB_fetch_array($TaxCatQuery)) {
	    $TaxCategory = $TaxCatRow['taxcatid'];
    }
    $SQL = "SELECT taxgrouptaxes.calculationorder,
					taxauthorities.description,
					taxgrouptaxes.taxauthid,
					taxauthorities.taxglcode,
					taxgrouptaxes.taxontax,
					taxauthrates.taxrate,
					taxauthorities.taxglcodediscount
	    FROM taxauthrates INNER JOIN taxgrouptaxes ON
		    taxauthrates.taxauthority=taxgrouptaxes.taxauthid
		    INNER JOIN taxauthorities ON
		    taxauthrates.taxauthority=taxauthorities.taxid
	    WHERE taxgrouptaxes.taxgroupid=1
	    AND taxauthrates.dispatchtaxprovince=1
	    AND taxauthrates.taxcatid = " .$TaxCategory . "
	    ORDER BY taxgrouptaxes.calculationorder";
    $ErrMsg = _('Los impuestos y tasas para este producto no se pueden recuperar por que GetFreightTaxes:');
    $DbgMsg= _('El sql que fallo fue:');
    $GetTaxRatesResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);	
    if ($TaxCatRow = DB_fetch_array($GetTaxRatesResult)) {
	    $Taxglcode = $TaxCatRow['taxglcode'];
	    $TaxRate=$myrow['taxrate'];
    }else{
	$TaxRate=0;
    }
    if ($TaxRate>0){
	$preciocompratax=$preciocompra*$TaxRate;
    }else{
	$preciocompratax=0;
    }
    //actualiza el monto de impuesto para nota de devolucion
    $TaxTotal=$TaxTotal+$preciocompratax;
    // actualiza el porcentaje de devolucion para esta compra
    $solicitada=$val['qtyinvoiced'];
    $newporcentaje=($val['Cantidad']/$solicitada)*100;
    if ($val['porccompra']>0){	
	$SQL = "UPDATE purchorders
	    SET totalrefundpercentpurch = totalrefundpercentpurch + " . $newporcentaje . "
	    WHERE orderno = '" .$val['orderno'] . "'";
    }else{
	$SQL = "UPDATE purchorderdetails
	    SET totalrefundpercent = totalrefundpercent + " . $newporcentaje . "
	    WHERE podetailitem = '" .$val['podetailitem'] . "'";	
    }
  //  echo $SQL;
    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El stock por almacen no se puede modificar');
    $DbgMsg = _('El SQL para actualizar el registro de las existencias del almacen es:');
    $Result = DB_query($SQL, $db, $ErrMsg,$DbgMsg,true);
    //valido que no se compre mas de las que se estan solicitando
    $ProveedorCompraANT=$ProveedorCompra;
    $MonedaANT=$val['moneda'];
    $rateANT=$val['rate'];
}

//inserta el ultimo movimiento de proveedor

//if ($ProveedorCompraANT!=$ProveedorCompra){
    
	if ($CreditNo>0){
	    $entra=true;
	    //inserta monto de supptrans
	    $fechaemision=date("Y-m-d H:i:s");
	    $PeriodNo = GetPeriod($fechaemision, $db);		    
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
			    " . $_POST['UnidNeg'] .",
			    " . $tiponota . ",
			    '" . $ProveedorCompraANT. "',
			    '" . $fechaemision . "',
			    '" . $fechaemision . "',
			    " . -($totalProveedor) . ",
			    " . -$TaxTotal . ",
			    " . $OrderNo . ",
			    " . $rateANT. ",
			    '" . _('Nota de Devolucion de productos que registro ') .$_SESSION['UserID'].' '.$PeriodNo . 'Trans-' . $CreditNo . "',
			    '"  . $MonedaANT  . "',
			    '"  . $fechaemision  . "'
			    
	    
			    )";
	    $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la nota de credito para el proveedor no se realizo');
	    $DbgMsg = _('El SQL utilizado para el registro de la nota de credito es:');
	    $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
	    $CreditTransID = DB_Last_Insert_ID($db,'supptrans','id');
	    // inserta movimiento contable
	    $tipoproveedor = ExtractTypeSupplier($ProveedorCompraANT,$db);
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
                                    '" . $fechaemision . "',
                                    " . $PeriodNo . ",
                                    " . $ctaxtipoproveedor . ",
                                    '" . $ProveedorCompraANT. "',
                                    " . round(($totalProveedor)/$rateANT,2) . "
                            ," . $_POST['UnidNeg'] . ")";
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La cuenta por cobrar no se ha insertado, por que');
	    $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
            $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
	    
	    //inserta movimiento de impuesto
	    if($TaxTotal>0){
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
				    " . $Taxglcode . ", 
				    '" . $ProveedorCompraANT . "', 
				    " . -($TaxTotal/$rateANT) . ",
				    " .  $_POST['UnidNeg']. ")";
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El impuesto de la venta no se ha insertado, por que');
		$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	    }
	    $liga="PDFNoteReturns.php?&clave=devolucion";
	    $liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PDFNoteReturns.php?OrderNo='.$OrderNo.'&TransNo=' . $CreditNo .'&Type='.$tiponota.'&Tagref='.$_POST['UnidNeg'].'">'. _('Imprimir Nota Devolucion') . ' (' . _('Laser') . ')' .'</a>';
	    echo '<br>'.$liga.'<br>';
	    
	}
//}


$Result = DB_Txn_Commit($db);
if($entra==true){
    prnMsg( _('Se han realizado de manera exitosa las devoluciones de productos'),'sucess');
}else{
    prnMsg( _('Los productos seleccionados no generan devoluciones.'),'error');
}
?>