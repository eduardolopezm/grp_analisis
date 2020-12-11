<?php
if ($_POST['GeneraCompraAuto']==true){
    $ProveedorCompra=$_SESSION['PO']->SupplierID;
    $SQL="select * from locations where loccode='".$_SESSION['PO']->Location."'";
  // echo $ProveedorCompra;
    $Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
    $CompraAlmacenRow = DB_fetch_row($Resultalmacen);
    $DelAdd1 = $CompraAlmacenRow[2];
    $DelAdd2 = $CompraAlmacenRow[3];
    $DelAdd3 = $CompraAlmacenRow[4];
    $DelAdd4 = $CompraAlmacenRow[5];
    $DelAdd5 = $CompraAlmacenRow[6];
    $DelAdd6 = $CompraAlmacenRow[7];
    $Tagref = $CompraAlmacenRow[14];   
    if($_SESSION['PO']->CurrCode == "MXN"){
    	$rate = 1;
    }else{
    	$SQL = "SELECT rate
				FROM tipocambio
				WHERE fecha = '".date("Y-m-d")."'
				AND currency = '".$_SESSION['PO']->CurrCode."'";
    	$Result = DB_query($SQL, $db);
    	$myrow2 = DB_fetch_array($Result);
    	$rate = $myrow2['rate'];
    	
    }        
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
		    tagref,
    		currcode,
			systypeorder,
    		wo
		    )
	    VALUES(
		    '" . $_SESSION['PO']->SupplierID . "',
		    '" . 'Compra automatica generada por '.$_SESSION['UserID'] . "',
		    date_format(now(),'%Y-%m-%d'),
		    '".$rate."',
		    '" . $_SESSION['UserID'] . "',
		    '0',
		    '" . $_SESSION['PO']->Location . "',
		    '" . $DelAdd1 . "',
		    '" . $DelAdd2 . "',
		    '" . $DelAdd3 . "',
		    '" . $DelAdd4 . "',
		    '" . $DelAdd5 . "',
		    '" . $DelAdd6 . "',
		    '0',			
		    '0',
		    now(),";
	    $sql.="'" . 'Pending'. "',";
	    $sql.="		'Compra de Faltantes',
		    now(),
		    ".$Tagref.",
		    '".$_SESSION['PO']->CurrCode."',		
			".$_SESSION['PO']->Typeorder.",
			".$_SESSION['PO']->Wo."
	    )";
	    //echo $sql;
    $ErrMsg =  _('The purchase order header record could not be inserted into the database because');
    $DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
    $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
    $OrderNoCompraNew = DB_Last_Insert_ID($db,'purchorders','orderno');
    
    
    prnMsg( _('La Nueva Orden de Compra es:'.$OrderNoCompraNew),'sucess');
        // genera nueva orden de compra en purchoredetails
    // recorro el arreglo para saber si debe o no hacerse la act del prod
    foreach ($_SESSION['PO']->LineItems as $OrderLine) {
        if ($_POST['ItemwithPurch_'.$OrderLine->LineNo]==true){
            $sql="SELECT *
                  FROM purchorderdetails inner join purchorders on purchorderdetails.orderno=purchorders.orderno
                  WHERE purchorderdetails.orderno=".$PONo."
                       and (quantityord-quantityrecd)>0
                        AND supplierno='".$ProveedorCompra."'
                        AND itemcode='".$OrderLine->StockID."'";
	    //echo "sqlprov:".$sql."<br><br><br>";
            $ErrMsg =  _('No se pudieron obtener los datos del producto');
            $DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
            //$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	    $Resultpurch = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
        //if (DB_num_rows($result)>0){
            if (DB_num_rows($Resultpurch)>0){
                    //while ($myrow=DB_fetch_array($Result)){
		    //while ($myrow = DB_fetch_array($Result)) {
			while ($myrow=DB_fetch_array($Resultpurch)){
                        $sql = "SELECT
                                    stockmaster.description,
                                    purchdata.suppliers_partno,
                                    stockmaster.pkg_type,
                                    stockmaster.units,
                                    stockmaster.netweight,		
                                    stockmaster.kgs,
                                    stockmaster.volume,
                                    stockcategory.stockact,
                                    stockmanufacturer.manufacturerid,
                                    purchdata.price
                                    FROM purchdata INNER JOIN stockmaster
                                    ON purchdata.stockid=stockmaster.stockid
                                     left join stockmanufacturer on stockmanufacturer.manufacturerid=stockmaster.manufacturer
                                    ,stockcategory
                                   
                                    WHERE stockcategory.categoryid = stockmaster.categoryid
                                        AND purchdata.stockid='" . $OrderLine->StockID. "'
                                        AND purchdata.supplierno='".$ProveedorCompra."'";
                        $ErrMsg = _('El producto no tiene proveedor configurado') . ': ' . $val['Producto']  . ' ' . _('no hay resultados');
                        $DbgMsg = _('El SQL utilizado es');
                        $ResultProveedorcompra =DB_query($sql,$db,$ErrMsg,$DbgMsg);
                        $myrowdatosc = DB_fetch_row($ResultProveedorcompra);
                        $_POST['ItemDescription'] = $myrowdatosc[0];
                        $_POST['suppliers_partno'] = $myrowdatosc[1];
                        $_POST['package'] = 1;//$myrow[2];
                        $_POST['uom'] = $myrowdatosc[3];
                        $_POST['nw'] = $myrowdatosc[4];
                        $_POST['gw'] = $myrowdatosc[5];
                        $_POST['cuft'] = $myrowdatosc[6];
                        $CompraStockidglcode=$myrowdatosc[7];
                        $price=$myrow[9];
                        //inserta detalle de orden de compra
                       // $cantidadsol=$val['Cantidad'];
                        // AGREGAR EL DESCUENTO Y % DE DEVOLUCION
                        // verifica el porcetaje de devolucion
                        $percentdevolucion=TraePercentDevXSupplier($ProveedorCompra,$myrow['itemcode'],$myrowdatosc[8],$_SESSION['Items'.$identifier]->DefaultSalesType,$db);
                        //echo $percentdevolucion;
                        $separa = explode('|',$percentdevolucion);
                        $Devolucion = $separa[0]/100;
                        $Discount=$separa[1]/100;
                        $totalpurch=$separa[2];
                        $cantidadsol=$myrow['quantityord'] - $myrow['quantityrecd'];
                        $sql = "INSERT INTO purchorderdetails (
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
                        			wo,
                        			qtywo,
                        			womasterid,
                        			wocomponent
                                    )
                                VALUES (
                                    " . $OrderNoCompraNew . ",
                                    '" .$myrow['itemcode'] . "',
                                    now(),
                                    '" . $myrow['itemdescription'] . "',
                                    '" . $CompraStockidglcode . "',
                                    '" .$OrderLine->Price. "',
                                    " . ($cantidadsol). ",
                                    0,
                                    0,
                                    '" . $_POST['ItemDescription'] . "',
                                    '" . $_POST['uom'] . "',
                                    '" . $_POST['suppliers_partno'] . "',
                                    '" . $POLine->subtotal_amount . "',
                                    '" . $_POST['package']. "',
                                    '" . $POLine->pcunit . "',
                                    '" . $_POST['nw'] . "',
                                    '" . $_POST['gw'] . "',
                                    '" . $_POST['cuft'] . "',					
                                    '" . ($cantidadsol) . "',
                                    '0',
                                    '0',
                                    '0',
                                    '0',
                                    '" . trim(DB_escape_string((htmlspecialchars_decode($OrderLine->Narrative,ENT_NOQUOTES)))) . "',
                                    'Orden de compra automatica',
                                    '" . ($Devolucion) . "',
                                    '" . $_SESSION['PO']->Wo. "'	,
                                    '" .  $myrow['qtywo']. "'	,
                                    '" .  $myrow['womasterid']. "',	
                                    '" .  $myrow['wocomponent']. "'		
                                    )";
                            $ErrMsg =_('No se pudo insertar el detalle de la orden de compra');
                            $DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
                            $Result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
                            
                            
                            // actualizo la cantidad solicitada a la cantidad recibida para que quede la orden de compra completaa
                            $SQL="UPDATE purchorderdetails
                                  SET quantityord=quantityrecd,
                                  completed=1
                                  WHERE podetailitem=".$myrow['podetailitem'];
                            $ErrMsg =_('No se pudo insertar el detalle de la orden de compra');
                            $DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
                            $Result =DB_query($SQL,$db,$ErrMsg,$DbgMsg);
                            
                            // Actualizo las requisisciones si es q esta ligada
                         /*   $sql="SELECT transferrequistions.quantityord,transferrequistions.quantityrecd, podetailitem
                                  FROM requisitionorderdetails inner join transferrequistions on  transferrequistions.norequisition=requisitionorderdetails.podetailitem
                                  WHERE transferrequistions.purchorderno = ".$_SESSION['PO']->OrderNo."
                                    AND requisitionorderdetails.itemcode='".$myrow['itemcode']."'
                                    AND grnbatch=".$GRN."
                                    AND (transferrequistions.quantityord-transferrequistions.quantityrecd)>0
                                  Order by priority desc ,requisitionorderdetails.orddate asc";
			    //echo $sql;
                            $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de requisicion de compra no se pudo obtener');
                            $DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de compra');
                            $ResultCompra = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
                            while ($myrowdos=DB_fetch_array($ResultCompra)){
                                $cantidadfaltante=$myrowdos['quantityord']-$myrowdos['quantityrecd'];
                                
                                $sql = "UPDATE transferrequistions
                                        SET quantityord=quantityrecd
                                        WHERE norequisition=".$myrowdos['podetailitem']."
                                                AND purchorderno=".$_SESSION['PO']->OrderNo."
                                                AND grnbatch=".$GRN;
                                $ErrMsg = _('No se puede insertar en el log');
                                $DbgMsg = _('El SQL es');
                                $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
                                // se inserta una nueva orden de compra para esta requisiscion
                                // se actualiza la requisicion con el numero de traspaso
                            $sql = "INSERT INTO transferrequistions (norequisition,
                                                    purchorderno,
                                                    type,
                                                    quantityord,
                                                    quantityrecd
                                                    )
                                    VALUES ('".$myrowdos['podetailitem']."',
                                            '" .  $OrderNoCompraNew ."',
                                            'Compra',
                                            '" .$cantidadfaltante. "',
                                            0
                                            )";
                            $ErrMsg = _('No se puede insertar en el log');
                            $DbgMsg = _('El SQL es');
                            $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
                            
                            $sql = "UPDATE requisitionorderdetails
                                    SET statusdetail='En Compra'
                            WHERE podetailitem='" . $myrow['podetailitem'] . "'";
                            $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
                            
                            $sql = "INSERT INTO logrequisitionorders (norequisition,
                                                    itemcode,
                                                    status,
                                                    comments,
                                                    userid,
                                                    datetransaction
                                                    )
                                    VALUES ('".$myrowdos['podetailitem']."',
                                                    '" .   $myrow['itemcode']  . "',
                                                    'En Compra',
                                                    'Se Genero compra de producto faltante ".  $myrow['itemcode'].'X la Cantidad de '. $cantidadfaltante.' En El Almacen:'.$_SESSION['PO']->Location.'De La Compra:'.$OrderNoCompraNew  ."',
                                                    '" .  $_SESSION['UserID'] . "',
                                                    now()
                                            )";
                            $ErrMsg = _('No se puede insertar en el log');
                            $DbgMsg = _('El SQL es');
                            $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
                            
                            $NoRequisition=$myrowdos['podetailitem'];
                            $productoReq=$myrow['itemcode'];
                            $statusReq='En Almacen Virtual';
                            $comentariosReq="Se Genero compra de producto faltante ".  $OrderLine->StockID.'X la Cantidad de '. $cantidadfaltante.' En El Almacen:'.$_SESSION['PO']->Location.'De La Compra:'.$OrderNoCompraNew;
                            $UsuarioReq=$_SESSION['UserID'];
                            include('SendEmailChangeRequisitions.inc');
                            
                        }// fin de req
                        */
                }// fin de while de producto
            }// fin de if de prod
        }// finde if de checkbox
    }// fin de arregloo  
    //actualizar orden a completaaaaa
    $completedsql='SELECT SUM(completed) as completedlines,
						COUNT(podetailitem) as alllines
					FROM purchorderdetails 
					WHERE orderno='.$_SESSION['PO']->OrderNo;
	$completedresult=DB_query($completedsql,$db);
	$mycompletedrow=DB_fetch_array($completedresult);
	$status=$mycompletedrow['alllines']-$mycompletedrow['completedlines'];
	if ($status==0) {
		$sql='SELECT stat_comment FROM purchorders WHERE orderno='.$_SESSION['PO']->OrderNo;
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$comment=$myrow['stat_comment'];
		$date = date($_SESSION['DefaultDateFormat']);
		$StatusComment=$date.' - Order Completed'.'<br>'.$comment;
		$sql="UPDATE purchorders 
				SET status='"._('Completed')."',
				stat_comment='".$StatusComment."'
				WHERE orderno=".$_SESSION['PO']->OrderNo;
		//$result=DB_query($sql,$db);
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		//echo "<br>8.-" . $SQL;
	}
    //$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
            
}
?>